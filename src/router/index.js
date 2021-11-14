import Vue from "vue";
import VueRouter from "vue-router";
import Login from "../views/Login.vue";
import Home from "../views/Home.vue";
import Innsjekk from "../views/Innsjekk.vue";
import Medlemmer from "../views/Medlemmer.vue";

Vue.use(VueRouter);

const routes = [
  {
    path: "/",
    name: "Login",
    component: Login
  },
  {
    path: "/home",
    name: "Home",
    component: Home
  },
  {
    path: "/innsjekk",
    name: "Innsjekk",
    component: Innsjekk
  },
  {
    path: "/medlemmer",
    name: "medlemmer",
    component: Medlemmer
  }
];

const router = new VueRouter({
  mode: "history",
  base: process.env.BASE_URL,
  routes
});

export default router;
