<template>
  <v-container>
    <h1>Innlogging</h1>
    <v-text-field
      label="Medlemsnummer eller e-post på Min speiding"
      v-model="username"
    ></v-text-field>
    <v-text-field
      type="password"
      v-model="password"
      label="Passord"
    ></v-text-field>
    <v-btn :disabled="disable_btn" large color="primary" block @click="login"
      >Logg inn</v-btn
    >
    <p>Logg inn for å hente din QR-kode!</p>
    <v-alert type="error" v-model="error" dismissible
      >Feil brukernavn/passord</v-alert
    >
  </v-container>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      username: "",
      password: "",
      error: false,
      disable_btn: false
    };
  },
  methods: {
    login() {
      let vm = this;
      axios
        .get("https://min.speiding.no/api/authenticate", {
          params: {
            username: this.username,
            password: this.password,
            app_name: "Innsjekksløsning - Roverleir Agenda 2021",
            app_id: "agenda2021.innsjekkslosning",
            device_id: navigator.userAgent
          }
        })
        .then(res => {
          vm.disable_btn = true;

          // Riktig brukernavn/passord?
          if (res.data.token) {
            localStorage.setItem("user", JSON.stringify(res.data)); // Legg bruker i localStorage.
            // Sjekk om vedkommende har innsjekk-rettighet.
            axios
              .get(
                "https://min.speiding.no/api/check_permission?permission=ManageProjectCheckIn&body_type=project&body_id=2108",
                {
                  headers: {
                    Authorization: "Bearer " + res.data.token,
                    "Content-Type": "application/json"
                  }
                }
              )
              .then(res => {
                vm.data = res.data;
                if (res.data) {
                  // Vedkommende har innsjekk-rettighet --> Send til admin-grensesnitt.
                  localStorage.setItem("admin", 1);
                  vm.$router.push({ name: "Innsjekk" });
                } else {
                  // Vedkommende har IKKE innsjekk-rettighet --> Send til QR-kode-grensesnitt.
                  vm.$router.push({ name: "Home" });
                }
              });
          } else {
            vm.error = true;
            vm.disable_btn = false;
          }
        })
        .catch(() => {
          vm.disable_btn = false;
          vm.error = true;
        });
    }
  },
  mounted() {
    let user = localStorage.getItem("user");
    if (user) {
      let admin = localStorage.getItem("admin") == 1 ? true : false;
      if (admin) {
        this.$router.push({ name: "Innsjekk" });
      } else {
        this.$router.push({ name: "Home" });
      }
    }
  }
};
</script>

<style scoped>
p {
  margin-top: 30px;
  text-align: center;
}
</style>
