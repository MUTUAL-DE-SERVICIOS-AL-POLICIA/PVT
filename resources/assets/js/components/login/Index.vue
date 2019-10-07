<template>
  <v-container fluid fill-height id="background-page">
    <v-row align="center" justify="center" v-if="!$store.getters.user">
      <v-col cols="12" sm="8" md="4">
        <v-card class="elevation-12 pa-5">
          <v-img
            src="/img/logo.png"
            aspect-ratio="3.6"
            contain
          ></v-img>
          <v-card-title primary-title class="justify-center">
            <div class="display-1 font-weight-thin text-md-center text-uppercase">
              <p>PLATAFORMA VIRTUAL</p>
              <p>DE TRÁMITES</p>
            </div>
          </v-card-title>
          <v-spacer></v-spacer>
          <v-card-text>
            <v-form>
              <v-text-field
                class="pl-5 pr-5"
                v-validate="'required|min:4|max:255'"
                @keyup.enter="focusPassword()"
                v-model="auth.username"
                prepend-icon="mdi-account"
                label="Usuario"
                name="usuario"
                :error-messages="errors.collect('usuario')"
                autocomplete="on"
                autofocus
                required
              ></v-text-field>
              <v-text-field
                class="pl-5 pr-5 mb-3"
                v-validate="'required|min:4|max:255'"
                @keyup.enter="authenticate(auth)"
                v-model="auth.password"
                prepend-icon="mdi-key"
                label="Contraseña"
                type="password"
                autocomplete="on"
                ref="password"
                name="contraseña"
                :error-messages="errors.collect('contraseña')"
                required
              ></v-text-field>
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-btn
              @click="authenticate(auth)"
              primary
              large
              block
              color="secondary"
            > Ingresar </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  name: "login",
  data() {
    return {
      auth: {
        username: "",
        password: ""
      },
      error: null
    };
  },
  methods: {
    focusPassword() {
      this.$refs.password.focus()
    },
    async authenticate(auth) {
      try {
        if (await this.$validator.validateAll()) {
          let res = await axios.post(`auth`, auth)
          this.$store.dispatch('login', res.data)
          // this.$store.commit('login', res.data)
          this.$router.go({
            name: 'dashboard'
          })
        }
      } catch (e) {
        console.log(e)
        auth.password = ''
        this.focusPassword()
      }
    }
  }
}
</script>

<style>
#background-page {
  background: linear-gradient(to bottom, #263238 0%, #cfd8dc 100%);
  filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#6ba872', endColorstr='#07540f', GradientType=0 );
}
</style>
