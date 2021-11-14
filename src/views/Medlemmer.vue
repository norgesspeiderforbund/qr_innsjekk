<template>
  <v-container>
    <v-btn fixed right x-small color="primary" @click="tilbake">Tilbake</v-btn>
    <h1>Deltakeroversikt</h1>
    <v-text-field label="Søk" v-model="sok"></v-text-field>

    <v-expansion-panels>
      <v-expansion-panel
        v-for="member in filtered_members"
        :key="member.member_no"
      >
        <v-expansion-panel-header disable-icon-rotate>
          {{ member.name }}
          <template v-slot:actions>
            <v-icon :color="member.statuscolor">{{ member.statusicon }}</v-icon>
          </template>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <v-row>
            <v-col>
              <strong>Manuell inn/utsjekking</strong>
            </v-col>
            <v-spacer></v-spacer>
            <v-col>
              <v-chip x-small label :color="member.statuscolor">
                <v-icon left>{{ member.statusicon }}</v-icon>
                {{ member.status }}
              </v-chip>
            </v-col>
          </v-row>

          <v-btn
            block
            color="green"
            @click="sjekkinn(member.member_no, false, member.name)"
            >Sjekk inn</v-btn
          >
          <v-btn
            block
            color="red"
            @click="sjekkut(member.member_no, false, member.name)"
            >Sjekk ut</v-btn
          >
          <v-btn
            block
            color="blue"
            @click="sjekkut(member.member_no, true, member.name)"
            >Send på haik</v-btn
          >
          <v-btn
            block
            color="indigo"
            @click="sjekkinn(member.member_no, true, member.name)"
            >Tilbake i leir</v-btn
          >
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>

    <v-dialog v-model="ooerror">
      <v-card dark color="error">
        <v-card-title>Operasjonen feilet</v-card-title>
        <v-card-text>
          Det skjedde en feil, men ta det med ro. Hendelsen er logget, og det
          blir ført manuelt.
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-btn text @click="ooerror = false">Lukk</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-snackbar v-model="snackbar" color="success" :timeout="2000">{{
      snackbar_msg
    }}</v-snackbar>

    <v-dialog v-model="oerror">
      <v-card dark color="error">
        <v-card-title>Operasjonen feilet</v-card-title>
        <v-card-text>
          Det skjedde en feil, og medlemmene kan ikke hentes.
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-btn text @click="oerror = false">Lukk</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-overlay :value="loading">
      <v-progress-circular indeterminate size="64"></v-progress-circular>
    </v-overlay>
  </v-container>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      members: [],
      token: "",
      mittnavn: "",
      oerror: false,
      ooerror: false,
      loading: true,
      snackbar_msg: "",
      snackbar: false,
      sok: ""
    };
  },
  methods: {
    tilbake() {
      this.$router.push("/innsjekk");
    },
    sjekkinn(member_no, haik, member_name) {
      let vm = this;
      axios
        .post(
          "/api/innsjekk.php",
          {
            member_no,
            token: vm.token,
            mittnavn: vm.mittnavn,
            checkin: true,
            haik
          },
          {
            headers: {
              "Content-Type": "application/json"
            }
          }
        )
        .then(res => {
          if (!res.data.success) {
            this.ooerror = true;
          }
          this.snackbar_msg = haik
            ? member_name + " er tilbake i leir"
            : member_name + " er sjekket inn";
          this.snackbar = true;
        });
    },
    sjekkut(member_no, haik, member_name) {
      let vm = this;
      axios
        .post(
          "/api/innsjekk.php",
          {
            member_no,
            token: vm.token,
            mittnavn: vm.mittnavn,
            checkin: false,
            haik
          },
          {
            headers: {
              "Content-Type": "application/json"
            }
          }
        )
        .then(res => {
          if (!res.data.success) {
            this.ooerror = true;
          }
          this.snackbar_msg = haik
            ? member_name + " er sendt på haik"
            : member_name + " er sjekket ut";
          this.snackbar = true;
        });
    }
  },
  mounted() {
    let user = JSON.parse(localStorage.getItem("user"));
    this.mittnavn = user.member.first_name + " " + user.member.last_name;
    this.token = user.token;

    // Sjekk om vedkommende har SeeMembers-rettighet. For haikefunksjon.
    let vm = this;
    axios.get("/api/members.php?token=" + user.token).then(res => {
      if (res.data.includes("error") && res.data.error) {
        vm.oerror = true;
      } else {
        vm.members = res.data;
        vm.loading = false;
      }
    });
  },
  computed: {
    filtered_members() {
      if (this.sok == "") return this.members;

      return this.members.filter(memb => {
        return memb.name.toLowerCase().includes(this.sok.toLowerCase());
      });
    }
  }
};
</script>

<style>
.v-btn,
.v-chip {
  margin-bottom: 10px;
}
</style>
