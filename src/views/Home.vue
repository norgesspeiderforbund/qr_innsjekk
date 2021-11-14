<template>
  <v-container>
    <h1>Hei, {{ first_name }}!</h1>
    <v-card elevation="10">
      <v-card-text>
        Velkommen til Slagplan! Hold denne koden klar ved innsjekken, så kommer
        dette til å gå glatt!
        <center>
          <vue-qrcode width="250" :value="code" />
        </center>
      </v-card-text>
    </v-card>
    <v-btn block color="primary" @click="loggut">Logg ut</v-btn>
  </v-container>
</template>

<script>
import VueQrcode from "vue-qrcode";

export default {
  name: "Home",
  data() {
    return {
      first_name: "",
      last_name: "",
      token: "",
      member_no: "",
      data: ""
    };
  },
  computed: {
    code() {
      return btoa(
        "SLAGPLAN2021#" +
          this.first_name +
          " " +
          this.last_name +
          "#" +
          this.member_no
      );
    }
  },
  methods: {
    loggut() {
      localStorage.clear();
      this.$router.push("/");
    }
  },
  components: {
    VueQrcode
  },
  mounted() {
    let vm = this;
    let user = JSON.parse(localStorage.getItem("user"));
    vm.first_name = user.member.first_name;
    vm.last_name = user.member.last_name;
    vm.member_no = user.member.member_no;
    vm.token = user.token;
  }
};
</script>
