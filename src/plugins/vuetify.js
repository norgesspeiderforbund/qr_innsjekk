import Vue from "vue";
import Vuetify from "vuetify/lib/framework";

Vue.use(Vuetify);

export default new Vuetify({
  theme: {
    themes: {
      light: {
        primary: "#f49038",
        secondary: "#ffffff",
        accent: "#000000",
        error: "#b71c1c"
      }
    }
  }
});
