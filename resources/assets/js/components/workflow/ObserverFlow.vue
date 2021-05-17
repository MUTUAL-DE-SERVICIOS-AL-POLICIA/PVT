<template>
  <v-container fluid class="pl-0 pt-0">
    <v-row justify="center" class="py-0">
      <v-col cols="12" class="py-0">
        <v-row justify="center" class="py-0">
          <v-col cols="12" class="py-0">
            <v-tabs dark active-class="secondary">
              <v-tab>OBSERVACIONES</v-tab>
              <v-tab-item>
                <v-card flat tile>
                  <v-card-text class="pl-10">
                    <v-col cols="12" class="pl-3">
                      <v-data-table
                        :headers="headersObs"
                        :items="observations"
                        :items-per-page="6"
                        class="elevation-1"
                      >
                        <template v-slot:item="items">
                          <tr>
                            <td>{{items.item.user_name}}</td>
                            <td>{{observation_type.find(o => o.id == items.item.observation_type_id).name }}</td>
                            <td>{{items.item.message}}</td>
                            <td>{{items.item.date|datetime}}</td>
                            <td>
                              <v-tooltip top>
                                <template v-slot:activator="{ on }">
                                  <v-btn
                                    v-if="!(items.item.user_id==$store.getters.id) && !items.item.enabled"
                                    icon
                                    small
                                    color="info"
                                    @click.stop="bus.$emit('openDialog', {...items.item, ...{edit: true}})"
                                    v-on="on"
                                  >
                                    <v-icon>mdi-pencil</v-icon>
                                  </v-btn>
                                </template>
                                <span class="caption">Editar</span>
                              </v-tooltip>

                              <v-tooltip top>
                                <template v-slot:activator="{ on }">
                                  <v-btn
                                    v-if="(items.item.user_id==$store.getters.id) && !items.item.enabled"
                                    icon
                                    small
                                    color="error"
                                    v-on="on"
                                    @click.stop="deleteObservation(
                                      items.item.user_id,
                                      items.item.observation_type_id,
                                      items.item.message,
                                      items.item.date,
                                      items.item.enabled)"
                                  >
                                    <v-icon>mdi-delete</v-icon>
                                  </v-btn>
                                </template>
                                <span class="caption">Eliminar</span>
                              </v-tooltip>
                            </td>
                          </tr>
                        </template>
                      </v-data-table>
                    </v-col>
                  </v-card-text>
                </v-card>
              </v-tab-item>
              <v-tab>HISTORIAL DEL TRÁMITE</v-tab>
              <v-tab-item >
                <v-card flat tile>
                  <v-card-text>
                    <v-col cols="12" class="mb-0">
                      <v-data-table
                        :headers="headersHist"
                        :items="record"
                        :items-per-page="6"
                        class="elevation-1"
                      >
                        <template v-slot:item="items">
                          <tr>
                            <td>{{items.item.created_at|datetime}}</td>
                            <td>{{items.item.updated_at|datetime}}</td>
                            <td>{{items.item.action}}</td>
                          </tr>
                        </template>
                      </v-data-table>
                    </v-col>
                  </v-card-text>
                </v-card>
              </v-tab-item>

              <v-tab>HISTORIAL DE PAGOS</v-tab>
              <v-tab-item >
                <v-card flat tile>
                  <v-card-text>
                    <v-col cols="12" class="mb-0">
                      <!--<v-data-table
                        :headers="headersHist2"
                        :items="record_payment"
                        :items-per-page="6"
                        class="elevation-1"
                      >
                        <template v-slot:item="items">
                          <tr>
                            <td>{{items.item.created_at|datetime}}</td>
                            <td>{{items.item.updated_at|datetime}}</td>
                            <td>{{items.item.action}}</td>
                          </tr>
                        </template>
                      </v-data-table>-->
                      <HistoryPayments :affiliate.sync="affiliate" :loan="loan"  />
                    </v-col>
                  </v-card-text>
                </v-card>
              </v-tab-item>

            </v-tabs>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
    <AddObservation :bus="bus" :loan="loan"  />
    <RemoveItem :bus="bus" />
  </v-container>
</template>

<script>
import AddObservation from "@/components/workflow/AddObservation"
import RemoveItem from "@/components/shared/RemoveItem"
import HistoryPayments from "@/components/payment/HistoryPayments"

export default {
  name: "observer-flow",

  components: {
    AddObservation,
    RemoveItem,
    HistoryPayments
  },
  data: () => ({
    //valor: false,
    observation_type: [],
    bus: new Vue(),
    headersHist: [
      {
        text: "Fecha creación",
        class: ["normal", "white--text"],
        align: "left",
        value: "created_at"
      },
      {
        text: "Fecha actualización",
        class: ["normal", "white--text"],
        align: "left",
        value: "update_at"
      },
      {
        text: "Acciones realizadas",
        class: ["normal", "white--text"],
        align: "left",
        value: "accion"
      }
    ],
      headersHist2: [
      {
        text: "Fecha creación",
        class: ["normal", "white--text"],
        align: "left",
        value: "created_at"
      },
      {
        text: "Fecha actualización",
        class: ["normal", "white--text"],
        align: "left",
        value: "update_at"
      },
      {
        text: "Acciones realizadas",
        class: ["normal", "white--text"],
        align: "left",
        value: "accion"
      }
    ],
    headersObs: [
      {
        text: "Usuario",
        class: ["normal", "white--text"],
        align: "left",
        value: "user_id"
      },
      {
        text: "Observación",
        class: ["normal", "white--text"],
        align: "left",
        value: "observation_type_id"
      },
      {
        text: "Mensaje",
        class: ["normal", "white--text"],
        align: "left",
        value: "message"
      },
      {
        text: "Fecha",
        class: ["normal", "white--text"],
        align: "left",
        value: "date"
      },
      {
        text: "Acciones",
        class: ["normal", "white--text"],
        align: "center",
        width: "11%",
        sortable: false
      }
    ],
    record: [],
    //record_payment: []
  }),
  props: {
    affiliate: {
      type: Object,
      required: true,
    },
    loan: {
      type: Object,
      required: true
    },
    observations: {
      type: Array,
      required: true
    },
    bus1: {
      type: Object,
      required: true
    },
  },

  mounted() {
    this.getObservationType()
    this.getRecords(this.loan.id)
    this.getRecordsPayment(this.loan.id)
    this.bus.$on("saveObservation", observation => {
      this.observations.unshift(observation)
    })
    this.bus.$on("emitSaveObservation", val => {//al realizar el guardado de la observacion en saveObservation
      this.bus1.$emit("emitGetObservation", this.loan.id) //emite a Add.vue para obtener los registros de las observaciones del id de prestamo
    })
  },
  methods: {
    async saveLoanRol(id) {
      try {
        let res = await axios.patch(`loan/${id}`, {
          role_id: this.valArea
        })
        this.toastr.success("Se devolvio satisfacctoriamente el tramite.")
        this.$router.push("/workflow")
        console.log("se cambio el rol")
      } catch (e) {
        console.log(e)
      }
    },
    async getObservationType() {
      try {
        this.loading = true
        let res = await axios.get(
          `module/${this.$store.getters.module.id}/observation_type`
        )
        this.observation_type = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getRecords(id) {
      try {
        this.loading = true
        let res = await axios.get(`record`, {
          params: {
            loan_id: id
          }
        })
        this.record = res.data.data
        console.log(this.record)
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    /*async getRecordsPayment(id) {
      try {
        this.loading = true
        let res = await axios.get(`record_payment`, {
          params: {
            loan_id: id
          }
        })
        this.record_payment = res.data.data
        console.log(this.record)
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },*/
    async deleteObservation(
      user_id1,
      observation_type_id1,
      message1,
      date1,
      enabled1
    ) {
      try {
        let res = await axios.delete(`loan/${this.loan.id}/observation`, {
          data: {
            user_id: user_id1,
            observation_type_id: observation_type_id1,
            message: message1,
            date: date1,
            enabled: enabled1
          }
        })
        this.toastr.success("La observación fue eliminada.")
        this.bus1.$emit("emitGetObservation", this.loan.id)
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>