<template>
  <v-container fluid>
    <ValidationObserver>
      <v-form>
        <v-card flat>
          <v-card-title class="pa-0">
            <v-toolbar dense color="tertiary" class="font-weight-regular">
              <v-toolbar-title>REPORTES</v-toolbar-title>
            </v-toolbar>
          </v-card-title>
          <pre></pre>
          <v-card-text>
            <v-tabs dark active-class="secondary" v-model="tab">
              <v-tab v-for="item in actions" :key="item.nameTab">{{item.nameTab}} </v-tab>
            </v-tabs>
            <v-tabs-items v-model="tab">
              <v-tab-item v-for="item in actions" :key="item.nameTab">
                <v-row align="center" no-gutters>
                  <v-col cols="12" md="6" sm="12" class="pa-2">
                    <v-card class="ma-0 pa-3">
                    <v-toolbar-title>
                      <b>Seleccione un reporte</b>
                    </v-toolbar-title>
                    <v-progress-linear class="mb-5"></v-progress-linear>
                    <v-select
                      dense
                      outlined
                      v-model="report_selected"
                      :items="computedReportsItems"
                      item-text="name"
                      return-object
                      label="Seleccione un reporte">
                    </v-select>
                    <!-- SOLAMENTE SE MOSTRARA CUANDO HAYA UN REPORTE SELECCIONADO -->
                    <template v-if="report_selected && visible == true">
                      <template v-if="report_selected.criterios.length > 0">
                        <v-toolbar-title>
                          <b>Criterios de búsqueda</b>
                        </v-toolbar-title>
                      <v-progress-linear class="mb-5"></v-progress-linear>
                      </template>
                      <template v-if="report_selected.criterios.includes('initial_date')">
                        <v-text-field
                          dense
                          v-model="report_inputs.initial_date"
                          label="Desde fecha"
                          hint="Día/Mes/Año"
                          type="date"
                          outlined
                        ></v-text-field>
                      </template>
                      <template v-if="report_selected.criterios.includes('final_date')">
                        <v-text-field
                          dense
                          v-model="report_inputs.final_date"
                          label="Hasta fecha"
                          hint="Día/Mes/Año"
                          type="date"
                          :min="report_inputs.initial_date"
                          outlined
                        ></v-text-field>
                      </template>
                      <template v-if="report_selected.criterios.includes('date')">
                        <v-text-field
                          dense
                          v-model="report_inputs.date"
                          :label="report_selected.label"
                          hint="Día/Mes/Año"
                          type="date"
                          outlined
                        ></v-text-field>
                      </template>
                      <template v-if="report_selected.criterios.includes('origin')">
                        <v-select
                          dense
                          :items="type_institution"
                          item-text="name"
                          item-value="value"
                          label= "Tipo institución"
                          v-model= report_inputs.origin
                          outlined
                        >
                        </v-select>
                      </template>
                      <template v-if="report_selected.criterios.includes('criteria_date')">
                        <v-row>
                          <v-col cols="6">
                            <v-text-field
                              dense
                              v-model="criteria_date.year"
                              label="Gestión"
                              append-outer-icon="mdi-chevron-right"
                              prepend-icon="mdi-chevron-left"
                              @click:append-outer="appendIconCallBack"
                              @click:prepend="prependIconCallBack"
                              readonly
                              outlined
                            ></v-text-field>
                          </v-col>

                          <v-col cols="6">
                            <v-select
                              dense
                              :items="months"
                              v-model="criteria_date.month"
                              item-text="name"
                              item-value="id"
                              label="Mes"
                              outlined
                            ></v-select>
                          </v-col>
                        </v-row>
                      </template>

                      <v-btn
                        color="primary"
                        :loading="loading_button"
                        @click.stop="downloadReport()"
                        >Descargar reporte</v-btn>
                    </template>
                    </v-card>
                  </v-col>
                </v-row>
              </v-tab-item>
            </v-tabs-items>
          </v-card-text>
        </v-card>
      </v-form>
    </ValidationObserver>
  </v-container>
</template>

<script>
export default {
  name: "Reports",
  data: () => ({
    tab: null,
    actions: [
      { nameTab: "Reportes de Préstamos", value: "prestamos" },
      { nameTab: "Reportes de Amortizaciones", value: "amortizaciones" },
      { nameTab: "Otros Reportes", value: "otros" },
    ],
    months: [
      {id:1, code:"01", name:"Enero"},
      {id:2, code:"02", name:"Febrero"},
      {id:3, code:"03", name:"Marzo"},
      {id:4, code:"04", name:"Abril"},
      {id:5, code:"05", name:"Mayo"},
      {id:6, code:"06", name:"Junio"},
      {id:7, code:"07", name:"Julio"},
      {id:8, code:"08", name:"Agosto"},
      {id:9, code:"09", name:"Septiembre"},
      {id:10, code:"10", name:"Octubre"},
      {id:11, code:"11", name:"Noviembre"},
      {id:12, code:"12", name:"Dciembre"}
    ],
    loading_button: false,
    // Reports
    reports_items: [],
    report_selected: null,
    report_inputs: {
      initial_date: null,
      final_date: null,
      date: null,
      origin: null
    },
    type_institution: [],
    visible: false,
    criteria_date: {
      year: new Date().getFullYear(),
      month: new Date().getMonth() + 1
    }

  }),

  created() {
    this.reports_items = [
      {
        id: 1,
        name: "Rep. Amortizaciones de descuentos por entidad",
        tab: 1,
        criterios: ["initial_date", "final_date"],
        service: "/report_amortization_discount_months",
        type: "xls",
        permissions: 'show-report-collections'
      },
      {
        id: 2,
        name: "Rep. Amortizaciones en efectivo y deposito en cuenta",
        tab: 1,
        criterios: ["initial_date", "final_date"],
        service: "/report_amortization_cash_deposit",
        type: "xls",
        permissions: 'show-report-collections'
      },
      {
        id: 3,
        name: "Rep. Amortizaciones por ajustes",
        tab: 1,
        criterios: ["initial_date", "final_date"],
        service: "/report_amortization_ajust",
        type: "xls",
        permissions: 'show-report-collections'
      },
      {
        id: 4,
        name: "Rep. Amortizaciones pendientes de confirmacion de acuerdo al comprobante de generacion",
        tab: 1,
        criterios: ["initial_date", "final_date"],
        service: "/report_amortization_pending_confirmation",
        type: "xls",
        permissions: 'show-report-collections'
      },
      {
        id: 5,
        name: "Rep. Amortizaciones por complemento y fondo de retiro",
        tab: 1,
        criterios: ["initial_date", "final_date"],
        service: "/report_amortization_fondo_complement",
        type: "xls",
        permissions: 'show-report-collections'
      },
      {
        id: 6,
        name: "Rep. Préstamos desembolsados",
        tab: 0,
        criterios: ["initial_date", "final_date"],
        service: "/report_loan_vigent",
        type: "xls",
        permissions: 'show-report-loans-consult'
      },
      {
        id: 7,
        name: "Rep. Préstamos del estado de cartera",
        tab: 0,
        criterios: ["initial_date", "final_date"],
        service: "/report_loan_state_cartera",
        type: "xls",
        permissions: 'show-report-loans'
      },
      {
        id: 8,
        name: "Rep. Préstamos en mora",
        tab: 0,
        criterios: ["date"],
        service: "/report_loans_mora",
        label: "Fecha final",
        type: "xls",
        permissions: 'show-report-loans-consult'
      },
      {
        id: 9,
        name: "Rep. Préstamos amortizados mensualmente mediante descuentos por garantía",
        tab: 0,
        criterios: [],
        service: "/loan_defaulted_guarantor",
        type: "xls",
        permissions: 'show-report-loans'
      },
      {
        id: 10,
        name: "Rep. Préstamos vigentes simultaneos en PVT y SISMU",
        tab: 0,
        criterios: ["date"],
        service: "/loan_pvt_sismu_report",
        label: "Fecha final",
        type: "xls",
        permissions: 'show-report-loans'
      },
      {
        id: 11,
        name: "Rep. Información para solicitud de descuentos Nuevos - Recurrentes",
        tab: 0,
        criterios: ["date"],
        service: "/loan_information",
        label:"Periódo (Seleccione el último dia del mes)",
        type: "xls",
        permissions: 'show-report-loans'
      },
      {
        id: 12,
        name: "Rep. Estado de solicitud de Préstamos (PDF)",
        tab: 2,
        criterios: ["date"],
        service: "/request_state_report",
        label: "Hasta fecha",
        type: "pdf",
        permissions: 'show-report-others'
      },
      {
        id: 13,
        name: "Rep. Estado de solicitud de Préstamos (XLS)",
        tab: 2,
        criterios: ["date"],
        service: "/request_state_report",
        label: "Hasta fecha",
        type: "xls",
        permissions: 'show-report-others'
      },
      {
        id: 14,
        name: "Rep. de salidas fondo rotatorio Préstamos",
        tab: 0,
        criterios: ["initial_date","final_date"],
        service: "/disbursements_fund_rotatory_outputs_report",
        type: "pdf",
        permissions: 'show-report-treasury'
      },
      {
        id: 15,
        name: "Rep. de amortizaciones realizados en caja",
        tab: 1,
        criterios: ["initial_date","final_date"],
        service: "/treasury_report",
        type: "pdf",
        permissions: 'show-report-treasury'
      },
      {
        id: 16,
        name: "Rep. Solicitudes de Préstamos (PDF)",
        tab: 2,
        criterios: ["initial_date","final_date"],
        service: "/loan_application_status",
        type: "pdf",
        permissions: 'show-report-others'
      },
      {
        id: 17,
        name: "Rep. Solicitudes de Préstamos (XLS)",
        tab: 2,
        criterios: ["initial_date","final_date"],
        service: "/loan_application_status",
        type: "xls",
        permissions: 'show-report-others'
      },
      {
        id: 18,
        name: "Rep. Dias transcurridos desde la ultima Amortización",
        tab: 0,
        criterios: ["final_date"],
        service: "/loans_days_amortization",
        type: "xls",
        permissions: 'show-report-others'
      },
      {
        id: 19,
        name: "Rep. de tramites procesados",
        tab: 0,
        criterios: ["initial_date", "final_date"],
        service: "/processed_loan_report",
        type: "xls",
        permissions: 'show-report-others'
      },
      {
        id: 20,
        name: "Rep. de Ingresos",
        tab: 0,
        criterios: ["initial_date", "final_date"],
        service: "/report_loans_income",
        type: "xls",
        permissions: 'show-report-others'
      },
      {
        id: 21,
        name: "Rep. de Prestatarios con Pagos Parciales",
        tab: 0,
        criterios: ["criteria_date"],
        service: "/report_loans_pay_partial",
        type: "xls",
        permissions: 'show-report-loans'
      },
      {
        id: 22,
        name: "Rep. de Prestatarios con Observaciones",
        tab: 2,
        criterios: [],
        service: "/affiliate_observation_report",
        type: "xls",
        permissions: 'show-report-loans'
      },
      {
        id: 23,
        name: "Rep. de Préstamos Vigentes con/sin Pagos Penales",
        tab: 0,
        criterios: [],
        service: "/loan_with_penal_payment_report",
        type: "xls",
        permissions: 'show-report-loans'
      },
    ],
    this.type_institution= [
      { value:"C", name:"Comando" },
      { value:"S", name: "Senasir" }
    ]
  },

  watch:{
   tab: function(newVal, oldVal) {
      if(newVal != oldVal) {
        this.visible = false
        this.clearInputs()
      }
      else {
        this.visible = true
      }
    },
    report_selected: {
      deep: true,
      handler(val) {
        this.visible = true
      }
    },
  },

  methods: {

    clearInputs() {
      this.report_selected = null
      this.report_inputs.initial_date = this.$moment(Date.now()).format('YYYY-MM-DD')
      this.report_inputs.final_date = this.$moment(Date.now()).format('YYYY-MM-DD')
      this.report_inputs.date = this.$moment(Date.now()).format('YYYY-MM-DD')
      this.report_inputs.origin = null,
      this.criteria_date = {
        year: new Date().getFullYear(),
        month: new Date().getMonth() + 1
      }
    },

    async downloadReport() {
      if (this.report_selected) {
        if(this.report_selected.type == 'xls'){
            const formData = new FormData();
            this.report_selected.criterios.forEach((criterio) => {
              let respuesta = this.report_inputs[criterio];
              //Verifica e introduce si existe respuesta de los criterios
              if (respuesta != null) {
                formData.append(criterio, this.report_inputs[criterio]);
              } else {
                console.log("Faltan criterios");
              }
          });
          this.loading_button = true
          await axios({
            url: this.report_selected.service,
            method: "GET",
            responseType: "blob", // important
            headers: { Accept: "application/vnd.ms-excel" },
            data: formData,
            params: {
              initial_date: this.report_inputs.initial_date,
              final_date: this.report_inputs.final_date,
              date: this.report_inputs.date,
              origin: this.report_inputs.origin,
              type:'xlsx',
              year: this.criteria_date.year,
              month: this.criteria_date.month
            },
          })
            .then((response) => {
              console.log(response.data);
              if(response.status==201 || response.status == 200){
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", this.report_selected.name + ".xlsx");
                document.body.appendChild(link);
                link.click();
                this.clearInputs();
              } else{
                this.toastr.error("Seleccione los criterios de búsqueda.");
              }
            })
            .catch((e) => {
              console.log(e);
              this.loading_button = false;
              this.toast.error("Favor seleccione los criterios de búsqueda.")
            });
          this.loading_button = false;
        } else{
          try {
            this.loading_button = true
            let res = await axios.get(`${this.report_selected.service}`, {
              params: {
                initial_date: this.report_inputs.initial_date,
                final_date: this.report_inputs.final_date,
                date: this.report_inputs.date,
                origin: this.report_inputs.origin,
                type:'pdf'
              },
            });
            printJS({
              printable: res.data.content,
              type: res.data.type,
              file_name: res.data.file_name,
              base64: true,
            });
            this.loading_button = false
          } catch (e) {
            this.loading_button = false
            this.toastr.error("Ocurrió un error en la impresión, seleecione los criterios de búsqueda.");
            console.log(e);
          }
        }
      } else {
        this.toastr.error("Seleccione un reporte.")
        this.loading_button = false
      }
    },
    appendIconCallBack (){
      this.criteria_date.year ++
    },
    prependIconCallBack (){
      this.criteria_date.year--
    }
  },

  computed: {
    //permisos del selector global por rol
    permissionSimpleSelected() {
      return this.$store.getters.permissionSimpleSelected
    },

    computedReportsItems() {
      let permissionsMap = {
        'show-report-collections': [],
        'show-report-treasury': [],
        'show-report-others': [],
        'show-report-loans-consult': [],
        'show-report-loans': []
      };

      // Filtrar items por permiso
      Object.keys(permissionsMap).forEach(permission => {
        if (this.permissionSimpleSelected.includes(permission)) {
          permissionsMap[permission] = this.reports_items.filter(item => item.permissions === permission);
        }
      });
      // Concatenar todos los arrays de items
      let reports_items = Object.values(permissionsMap).flat();

      // Filtrar por la pestaña seleccionada
      return reports_items.filter(item => item.tab === this.tab);
    },
  },
};
</script>
