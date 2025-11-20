<template>
  <v-container fluid>
    <v-card>
      <v-row justify="center">
        <v-col cols="12" v-if="!this.reprogramming">
          <ValidationObserver ref="observerDestiny">
            <v-form>
              <v-container>
                <v-row>
                  <v-col cols="12" md="2">
                    <label>Tipo de Depósitos:</label>
                  </v-col>
                  <v-col cols="12" md="3">
                    <ValidationProvider v-slot="{ errors }" name="Tipo Desembolso" rules="required">
                      <v-select
                        class="py-0"
                        :error-messages="errors"
                        dense
                        v-model="loanTypeSelected"
                        :onchange="Onchange()"
                        :items="payment_types"
                        item-text="name"
                        item-value="id"
                        outlined
                      ></v-select>
                    </ValidationProvider>
                  </v-col>

                  <v-col cols="12" md="3" class="py-0" v-show="visible">
                    <ValidationProvider
                      v-slot="{ errors }"
                      vid="financial_entity_id"
                      name="Entidad Financiera"
                      :rules="loanTypeSelected == 1 ? 'required': ''"
                    >
                      <v-select
                        :error-messages="errors"
                        dense
                        :items="entity"
                        item-text="name"
                        item-value="id"
                        label="Entidad Financiera"
                        v-model="affiliate.financial_entity_id"
                        :readonly="!editable || !permission.secondary"
                        :outlined="editable && permission.secondary"
                        :disabled="editable && !permission.secondary"
                      ></v-select>
                    </ValidationProvider>
                  </v-col>
                  <v-col cols="12" md="3" class="py-0" v-show="visible">
                    <ValidationProvider
                      v-slot="{ errors }"
                      vid="account_number"
                      name="Cuenta SIGEP Activa"
                      :rules="loanTypeSelected == 1 ? 'required': ''"
                    >
                      <v-text-field
                        :error-messages="errors"
                        label="Cuenta SIGEP Activa"
                        class="py-0"
                        dense
                        v-model="affiliate.account_number"
                        :readonly="!editable || !permission.secondary"
                        :outlined="editable && permission.secondary"
                        :disabled="editable && !permission.secondary"
                      ></v-text-field>
                    </ValidationProvider>
                  </v-col>
                  <v-col cols="12" md="1" class="ma-0 pa-0" v-show="visible">
                    <v-tooltip top>
                      <template v-slot:activator="{ on }">
                        <v-btn
                          fab
                          dark
                          x-small
                          :color="'error'"
                          bottom
                          right
                          v-on="on"
                          style="margin-right: 45px;"
                          @click.stop="resetForm()"
                          v-show="editable"
                        >
                          <v-icon>mdi-close</v-icon>
                        </v-btn>
                      </template>
                      <div>
                        <span>Cancelar</span>
                      </div>
                    </v-tooltip>
                    <v-tooltip top>
                      <template v-slot:activator="{ on }">
                        <v-btn
                          fab
                          dark
                          x-small
                          :color="'light-blue accent-4'"
                          bottom
                          right
                          v-on="on"
                          style="margin-right: 45px;margin-top:5px;"
                          @click.stop="clear()"
                          v-show="editable"
                        >
                          <v-icon>mdi-eraser</v-icon>
                        </v-btn>
                      </template>
                      <div>
                        <span>Borrar</span>
                      </div>
                    </v-tooltip>
                    <v-tooltip top>
                      <template v-slot:activator="{ on }">
                        <v-btn
                          fab
                          dark
                          x-small
                          :color="editable ? 'danger' : 'success'"
                          bottom
                          right
                          v-on="on"
                          style="margin-right: -9px; margin-top:5px;"
                          @click.stop="saveAffiliate()"
                        >
                          <v-icon v-if="editable">mdi-check</v-icon>
                          <v-icon v-else>mdi-pencil</v-icon>
                        </v-btn>
                      </template>
                      <div>
                        <span v-if="editable">Guardar</span>
                        <span v-else>Editar</span>
                      </div>
                    </v-tooltip>
                  </v-col>
                  <v-col cols="12" md="6" v-show="espacio"></v-col>
                  <v-col cols="12" md="2" class="py-1">
                    <label>Destino del Préstamo:</label>
                  </v-col>
                  <v-col cols="12" md="6">
                    <ValidationProvider v-slot="{ errors }" name="destino" rules="required">
                      <v-select
                        class="py-0"
                        :error-messages="errors"
                        v-model="loanTypeSelected2"
                        dense
                        :items="destino"
                        item-text="name"
                        item-value="id"
                        outlined
                      ></v-select>
                    </ValidationProvider>
                  </v-col>
                </v-row>
              </v-container>
            </v-form>
          </ValidationObserver>
        </v-col>
          <ReferencePerson
          v-show="modalidad_personal_reference"
            :reference_person="reference_person"
          />
      </v-row>
    </v-card>
    <v-container>
          <v-row>
            <v-spacer></v-spacer><v-spacer></v-spacer><v-spacer></v-spacer>
              <v-col>
                <v-btn text
                @click="beforeStepBus(5)">Atras</v-btn>
                <v-btn
                color="primary"
                @click="validateStepsFive()">
                Siguiente
                </v-btn>
              </v-col>
            </v-row>
    </v-container>
  </v-container>
</template>
<script>
import ReferencePerson from "@/components/loan/ReferencePerson";
export default {
  name: "loan-information",
  components: {
    ReferencePerson
  },
  props: {
    loan_detail: {
      type: Object,
      required: true
    },
    modalidad_personal_reference: {
      type: Boolean,
      required: true,
      default: false
    },
    procedureLoan: {
      type: Object,
      required: true
    },
    bus: {
      type: Object,
      required: true
    },
    modalidad_id: {
      type: Number,
      required: true,
      default: 0
    },
    affiliate: {
      type: Object,
      required: true
    },
    modalidad_max_cosigner: {
      type: Number,
      required: true,
      default:0
    },
    data_loan_parent_aux: {
      type: Object,
      required: true
    }
  },
  data: () => ({
    cuenta: null,
    destino:[],
    visible: false,
    espacio: true,
    loanTypeSelected: null,
    loanTypeSelected2: null,
    payment_types: [],
    cities: [],
    entity: null,
    editedIndexPerRef: -1,
    personal_reference:{},
    val_destiny: false,
    val_per_ref: false,
    //reference: [],
    cosigners:[],
    editable: false,
    aux: {},
    reference_person:[]
  }),
  watch: {
    modalidad_id(newVal, oldVal){
      this.getLoanDestiny()
      this.aux.financial_entity_id = this.affiliate.financial_entity_id
      this.aux.number_payment_type = this.affiliate.account_number
    },
    modalidad_personal_reference() {
      if (this.modalidad_personal_reference == false) {
        this.personal_reference = {};
      }
    }
  },

  beforeMount() {
    this.getCities();
    this.getPaymentTypes();
  },

  computed: {
    //Metodo para obtener Permisos por rol
    permissionSimpleSelected () {
      return this.$store.getters.permissionSimpleSelected
    },
    permission() {
      return {
        primary: this.primaryPermission,
        secondary: this.secondaryPermission
      }
    },
    secondaryPermission() {
      if (this.affiliate.id) {
        return this.permissionSimpleSelected.includes(
          "update-affiliate-secondary"
        )
      } else {
        return this.permissionSimpleSelected.includes("create-affiliate")
      }
    },
    primaryPermission() {
      if (this.affiliate.id) {
        return this.permissionSimpleSelected.includes(
          "update-affiliate-primary"
        )
      } else {
        return this.permissionSimpleSelected.includes("create-affiliate")
      }
    },
    reprogramming() {
      return this.$route.params.hash == 'reprogramming' || this.data_loan_parent_aux.parent_reason =='REPROGRAMACIÓN'
    },
    remake() {
      return this.$route.params.hash === 'remake'
    },
  },
  methods: {
    beforeStepBus(val) {
      this.bus.$emit("beforeStepBus", val)
    },
    nextStepBus(val) {
      this.bus.$emit("nextStepBus", val)
    },
    Onchange() {
      for (let i = 0; i < this.payment_types.length; i++) {
        if (this.loanTypeSelected == this.payment_types[i].id) {
          if (this.payment_types[i].name == "Depósito Bancario") {
            this.visible = true
            this.espacio = false
            this.getEntity()
          } else {
            this.visible = false
            this.espacio = true
          }
        }
      }
      if(this.visible){
        this.loan_detail.payment_type_id = this.loanTypeSelected
        this.loan_detail.financial_entity_id = this.affiliate.financial_entity_id
        this.loan_detail.number_payment_type = this.affiliate.account_number
        this.loan_detail.destiny_id = this.loanTypeSelected2
      }else{
        this.loan_detail.payment_type_id = this.loanTypeSelected
        this.loan_detail.financial_entity_id = null
        this.loan_detail.number_payment_type = null
        this.loan_detail.destiny_id = this.loanTypeSelected2
      }
    },
    async getPaymentTypes() {
      try {
        this.loading = true;
        let res = await axios.get(`payment_type`);
        this.payment_types = res.data;
      } catch (e) {
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    async getCities() {
      try {
        this.loading = true;
        let res = await axios.get(`city`);
        this.cities = res.data;
      } catch (e) {
        this.dialog = false;
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    async getLoanDestiny() {
      try {
        this.loading = true
        let res = await axios.get(`procedure_type/${this.procedureLoan.procedure_id}/loan_destiny`)
        this.destino = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getEntity() {
      try {
        this.loading = true;
        let res = await axios.get(`financial_entity`);
        this.entity = res.data;
      } catch (e) {
        this.dialog = false;
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    async savePersonalReference() {
      try {
        let ids_reference = []
        if(this.modalidad_personal_reference){
          for (let i = 0; i < this.reference_person.length; i++) {
            let res = await axios.post(`personal_reference`, {
              last_name: this.reference_person[i].last_name,
              mothers_last_name: this.reference_person[i].mothers_last_name,
              first_name: this.reference_person[i].first_name,
              second_name: this.reference_person[i].second_name,
              phone_number: this.reference_person[i].phone_number,
              cell_phone_number: this.reference_person[i].cell_phone_number,
              address: this.reference_person[i].address,
              kinship_id: this.reference_person[i].kinship_id
          })
          ids_reference.push(res.data.id)
        }
        this.loan_detail.reference = ids_reference
        }
      } catch (e) {
        this.dialog = false
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async saveAffiliate() {
      try {
        if (!this.editable) {
          this.editable = true
        } else {
          await axios.patch(`affiliate/${this.affiliate.id}`, {
            financial_entity_id: this.affiliate.financial_entity_id,
            account_number: this.affiliate.account_number,
            sigep_status: (this.affiliate.financial_entity_id != null && this.affiliate.account_number != null) ? 'ACTIVO' : null
          })
          this.aux.financial_entity_id = this.affiliate.financial_entity_id
          this.aux.number_payment_type = this.affiliate.account_number
          this.toastr.success("Registro guardado correctamente")
          this.editable = false
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    resetForm() {
      this.editable = false
      this.affiliate.financial_entity_id = this.aux.financial_entity_id
      this.affiliate.account_number = this.aux.number_payment_type

    },
    clear(){
      this.affiliate.financial_entity_id = null
      this.affiliate.account_number= null
    },
    async validateStepsFive() {
      try {
        if (this.editable) {
          this.toastr.error("Por favor guarde los datos de la entidad financiera.");
          return;
        }

        if (!this.reprogramming) {
          this.val_destiny = await this.$refs.observerDestiny.validate();
          if (!this.val_destiny) {
            this.toastr.error("Falta el registro de algunos campos");
            return;
          }
        }

        if (this.modalidad_personal_reference) {
          if (this.reference_person.length < 1) {
            this.toastr.error("Registre por lo menos una persona de referencia");
            return;
          }
        }

        await this.savePersonalReference();
        this.nextStepBus(5);

      } catch (e) {
        if (this.$refs.observerDestiny) {
          this.$refs.observerDestiny.setErrors(e);
        }
        if (this.modalidad_personal_reference && this.$refs.observerPerRef) {
          this.$refs.observerPerRef.setErrors(e);
        }
        console.error("Ocurrio un error", e);
      }
    }
  }
};
</script>
