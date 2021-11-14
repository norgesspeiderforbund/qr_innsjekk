<template>
  <v-container>
    <h1>Innsjekkadmin</h1>
    <v-switch
      v-model="haik"
      @change="saveHaikState"
      :label="
        haik ? 'Sjekker inn/ut for haik' : 'Sjekker inn/ut av arrangement'
      "
    ></v-switch>
    <v-card elevation="10">
      <v-card-text>
        <center>
          <qrcode-stream
            :camera="camera"
            @decode="onDecode"
            @init="onInit"
          ></qrcode-stream>
        </center>
      </v-card-text>
    </v-card>
    <v-btn block color="primary" @click="haikeside">Se deltakere</v-btn>
    <v-btn block color="primary" @click="loggut">Logg ut</v-btn>

    <v-dialog v-model="dialog">
      <v-card>
        <v-card-title>{{ scanned_member.member_name }}</v-card-title>
        <v-card-text>
          Sjekke inn eller ut?
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-btn text color="success" @click="sjekkinn()">{{
            haik ? "Tilbake i leir" : "Sjekk inn"
          }}</v-btn>
          <v-spacer></v-spacer>
          <v-btn text color="error" @click="sjekkut()">{{
            haik ? "Ut på tur!" : "Sjekk ut"
          }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="ugyldig">
      <v-card dark color="error">
        <v-card-title>Skanning feilet</v-card-title>
        <v-card-text>
          Denne QR-koden kan ikke sjekke inn noen :(
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-btn text @click="ugyldig = false">Lukk</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="oerror">
      <v-card dark color="error">
        <v-card-title>Operasjonen feilet</v-card-title>
        <v-card-text>
          Det skjedde en feil, men ta det med ro. Hendelsen er logget, og det
          blir ført manuelt.
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-btn text @click="oerror = false">Lukk</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-snackbar v-model="snackbar" color="success" :timeout="2000"
      >{{ scanned_member.member_name }} er {{ snackbar_msg }}</v-snackbar
    >
  </v-container>
</template>

<script>
import axios from "axios";
import { QrcodeStream } from "vue-qrcode-reader";

export default {
  data() {
    return {
      dialog: false,
      mittnavn: "",
      ugyldig: false,
      oerror: false,
      token: "",
      haik: false,
      snackbar: false,
      snackbar_msg: "sjekket inn",
      scanned_member: {
        member_no: "",
        member_name: ""
      }
    };
  },
  methods: {
    onDecode(decodedString) {
      let code = atob(decodedString).split("#");
      if (code[0] != "SLAGPLAN2021") {
        this.ugyldig = true;
      } else {
        this.scanned_member.member_no = code[2];
        this.scanned_member.member_name = code[1];
        this.dialog = true;
      }
    },
    sjekkinn() {
      let vm = this;
      axios
        .post(
          "/api/innsjekk.php",
          {
            member_no: vm.scanned_member.member_no,
            token: vm.token,
            mittnavn: vm.mittnavn,
            checkin: true,
            haik: vm.haik
          },
          {
            headers: {
              "Content-Type": "application/json"
            }
          }
        )
        .then(res => {
          this.dialog = false;
          if (!res.data.success) {
            this.oerror = true;
          }
          this.snackbar_msg = this.haik ? "tilbake i leir" : "sjekket inn";
          this.snackbar = true;
        });
    },
    sjekkut() {
      let vm = this;
      axios
        .post(
          "/api/innsjekk.php",
          {
            member_no: vm.scanned_member.member_no,
            token: vm.token,
            mittnavn: vm.mittnavn,
            checkin: false,
            haik: vm.haik,
            seemembers: false
          },
          {
            headers: {
              "Content-Type": "application/json"
            }
          }
        )
        .then(res => {
          this.dialog = false;
          if (!res.data.success) {
            this.oerror = true;
          }
          this.snackbar_msg = this.haik ? "sendt på haik" : "sjekket ut";
          this.snackbar = true;
        });
    },
    loggut() {
      localStorage.clear();
      this.$router.push("/");
    },
    haikeside() {
      this.$router.push("/medlemmer");
    },
    saveHaikState() {
      if (this.haik) {
        localStorage.setItem("haikstate", "on");
      } else {
        localStorage.setItem("haikstate", "off");
      }
    },
    async onInit(promise) {
      try {
        await promise;
      } catch (error) {
        if (error.name === "NotAllowedError") {
          this.error = "ERROR: you need to grant camera access permisson";
        } else if (error.name === "NotFoundError") {
          this.error = "ERROR: no camera on this device";
        } else if (error.name === "NotSupportedError") {
          this.error = "ERROR: secure context required (HTTPS, localhost)";
        } else if (error.name === "NotReadableError") {
          this.error = "ERROR: is the camera already in use?";
        } else if (error.name === "OverconstrainedError") {
          this.error = "ERROR: installed cameras are not suitable";
        } else if (error.name === "StreamApiNotSupportedError") {
          this.error = "ERROR: Stream API is not supported in this browser";
        }
      }
    }
  },
  components: {
    QrcodeStream
  },
  computed: {
    camera() {
      if (this.dialog || this.ugyldig || this.oerror) return "off";
      else return "auto";
    }
  },
  mounted() {
    let user = JSON.parse(localStorage.getItem("user"));
    this.mittnavn = user.member.first_name + " " + user.member.last_name;
    this.token = user.token;

    // Sjekk om vedkommende har SeeMembers-rettighet. For haikefunksjon.
    let vm = this;
    axios
      .get(
        "https://min.speiding.no/api/check_permission?permission=SeeMembers&body_type=project&body_id=2108",
        {
          headers: {
            Authorization: "Bearer " + user.token,
            "Content-Type": "application/json"
          }
        }
      )
      .then(res => {
        vm.data = res.data;
        if (res.data) {
          // Vedkommende har SeeMembers; vis knapp.
          vm.seemembers = true;
        }
      });

    switch (localStorage.getItem("haikstate")) {
      case "on":
        this.haik = true;
        break;
      case "off":
        this.haik = false;
        break;
      default:
        break;
    }
  }
};
</script>

<style scoped>
p {
  margin-top: 30px;
  text-align: center;
}
.v-btn {
  margin-bottom: 10px;
}
</style>
