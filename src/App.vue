<template>
  <v-app>
    <v-app-bar app color="secondary">
      <div class="d-flex align-center">
        <v-img
          alt="Agenda Logo"
          class="shrink mr-2"
          contain
          src="@/assets/agendalogo.png"
          transition="scale-transition"
          width="80"
        />
        <v-toolbar-title color="secondary"
          >Innsjekk - Roverleir</v-toolbar-title
        >
      </div>
    </v-app-bar>

    <v-main>
      <router-view></router-view>
    </v-main>
  </v-app>
</template>

<script>
export default {
  name: "App",

  data: () => ({
    //
  })
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Lato:wght@400&display=swap");
* {
  font-family: "Lato", sans-serif;
}
.v-main {
  margin-top: 30px;
}
</style>
