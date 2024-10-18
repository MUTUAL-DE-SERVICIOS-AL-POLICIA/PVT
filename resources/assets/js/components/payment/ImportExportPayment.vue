<template>
  <v-container fluid>
      <v-card flat>
        <v-toolbar dense color="tertiary" class="font-weight-regular">
          <v-toolbar-title>GENERACIÓN DE PERIODOS </v-toolbar-title>
          <div class="flex-grow-1"></div>
                <v-btn-toggle
                  v-model="period_type"
                  color="teal"
                  group
                  mandatory
                >
                  <v-btn value="COMANDO">
                    Comando
                  </v-btn>
                  <v-btn value="SENASIR">
                    Senasir
                  </v-btn>
                  <v-btn value="ESTACIONAL">
                    Estacional
                  </v-btn>
                </v-btn-toggle>
                <v-select
                  :items="year"
                  item-text="year"
                  item-value="year"
                  :loading="loading"
                  label="Gestion"
                  v-model="period_year"
                  @change="Onchange()"
                  dense
                  outlined
                  class="select-year"
                ></v-select>
                <v-tooltip top>
                  <template v-slot:activator="{ on }">
                    <v-btn
                      fab
                      dark
                      x-small
                      :color="'success'"
                      bottom
                      right
                      v-on="on"
                      @click.stop="savePeriod()"
                      class="mx-2"
                    >
                      <v-icon>mdi-plus</v-icon>
                    </v-btn>
                  </template>
                  <div>
                    <span>Añadir Periodo</span>
                  </div>
                </v-tooltip>
        </v-toolbar>
      </v-card>
      <v-row justify="center" class="py-0 mt-2">
          <v-card class="headline font-weight-bold ma-2" max-width="200px"  v-for="(item, i) in month" :key="i">
            <v-card-title class="teal text-center">
              <v-row justify="center"> <h3 class="white--text"> {{meses[item.month - 1]}}</h3></v-row>
            </v-card-title>
            <v-progress-linear color="white"></v-progress-linear>          
            <v-card-text class="blue-grey lighten-5">
              <v-row v-if="period_type==='COMANDO'">
                <v-col cols="12" md="12" class="py-0">
                  <b>Comando <v-icon small>mdi-city</v-icon></b>
                </v-col>
                 <v-divider inset></v-divider>
                <v-col cols="12" md="12" class="py-0">
                    <v-tooltip top>
                        <template v-slot:activator="{ on }">
                          <v-btn
                            class="ma-2 teal white--text btn-period"
                            v-on="on"
                            :disabled="item.importation"
                            :loading="loading_sc && i == l_index"
                            @click.stop="l_index=i;solicitudComando('C', item.id,meses[item.month - 1])"
                          >
                          <v-icon dark left small>mdi-arrow-down</v-icon> Solicitud
                          </v-btn>
                        </template>
                        <div>
                          <span>Descargar Solicitud Comando</span>
                        </div>
                    </v-tooltip>
                    <v-tooltip top>
                      <template v-slot:activator="{ on }">
                        <v-btn
                          class="ma-2 info white--text btn-period"
                          v-on="on"
                          :disabled="item.importation"
                          @click.stop="importacionComando(item.month, item.id)"
                        >
                          <v-icon dark left small>mdi-arrow-up</v-icon>Importación
                        </v-btn>
                      </template>
                      <div>
                        <span>Importación Comando</span>
                      </div>
                    </v-tooltip>           
                </v-col>
              </v-row>
              <v-row v-if="period_type==='SENASIR'">
                <v-col cols="12" md="12" class="py-0">
                  <b>Senasir <v-icon small>mdi-home-analytics</v-icon></b>
                </v-col>
                <v-divider inset></v-divider>
                <v-col cols="12" md="12" class="py-0">
                    <v-tooltip top>
                      <template v-slot:activator="{ on}">
                        <v-btn
                          class="ma-2 teal white--text btn-period"
                          v-on="on"
                          :loading="loading_ss && i == l_index"
                          :disabled="item.importation"
                          @click.stop="l_index = i;solicitudSenasir('S', item.id,meses[item.month - 1])"
                        >
                          <v-icon  dark left small>mdi-arrow-down</v-icon>Solicitud
                        </v-btn>
                      </template>
                      <div>
                        <span>Solicitud Senasir</span>
                      </div>
                    </v-tooltip>
                    <v-tooltip top>
                    <template v-slot:activator="{ on}">
                      <v-btn
                        class="ma-2 info white--text btn-period"
                        v-on="on"
                        :disabled="item.importation"
                         @click.stop="importacionSenasir(item.month, item.id)"
                      >
                        <v-icon dark left small>mdi-arrow-up</v-icon>Importación
                      </v-btn>
                    </template>
                    <div>
                      <span>Importación Senasir</span>
                    </div>
                    </v-tooltip>
                </v-col>
              </v-row>
              <v-row v-if="period_type==='ESTACIONAL'">
                <v-col cols="12" md="12" class="py-0">
                  <b>Senasir <v-icon small>mdi-home-analytics</v-icon></b>
                </v-col>
                <v-divider inset></v-divider>
                <v-col cols="12" md="12" class="py-0">
                    <v-tooltip top>
                      <template v-slot:activator="{ on}">
                        <v-btn
                          class="ma-2 teal white--text btn-period"
                          v-on="on"
                          :loading="loading_ss && i == l_index"
                          :disabled="item.importation"
                          @click.stop="l_index = i;solicitudSenasir('S', item.id,meses[item.month - 1])"
                        >
                          <v-icon  dark left small>mdi-arrow-down</v-icon>Solicitud
                        </v-btn>
                      </template>
                      <div>
                        <span>Solicitud Senasir</span>
                      </div>
                    </v-tooltip>
                    <v-tooltip top>
                    <template v-slot:activator="{ on}">
                      <v-btn
                        class="ma-2 info white--text btn-period"
                        v-on="on"
                        :disabled="item.importation"
                         @click.stop="importacionEstacional(item.month, item.id)"
                      >
                        <v-icon dark left small>mdi-arrow-up</v-icon>Importación
                      </v-btn>
                    </template>
                    <div>
                      <span>Importación Senasir</span>
                    </div>
                    </v-tooltip>
                </v-col>
              </v-row>
            </v-card-text>
            <v-progress-linear color="white"></v-progress-linear>
            <v-card-actions class="blue-grey lighten-5">
              <v-spacer></v-spacer>
              <v-tooltip top v-if="period_type==='COMANDO'" class="my-0">
                <template v-slot:activator="{ on }">
                  <v-btn
                    :disabled="!item.importation"
                    small
                    :color="'primary'"
                    fab
                    v-on="on"
                    :v-model="report_button_command[i]"
                    :loading="report_loading_command[i]"
                    @click.stop="reporteComandoSenasir(item.id,'C', i)"
                  >
                    <v-icon>mdi-file-document</v-icon>
                  </v-btn>
                </template>
                <div>
                  <span>Reporte Pago Comando</span>
                </div>
              </v-tooltip>
              <v-tooltip top v-if="period_type==='SENASIR'">
                <template v-slot:activator="{ on }">
                  <v-btn
                    :disabled="!item.importation"
                    small
                    :color="'primary'"
                    fab
                    v-on="on"
                    v-model="report_button_senasir[i]"
                    :loading="report_loading_senasir[i]"
                    @click.stop="reporteComandoSenasir(item.id,'S',i)"
                  >
                    <v-icon >mdi-file-document</v-icon> 
                  </v-btn>
                </template>
                <div>
                  <span>Reporte Pago Senasir</span>
                </div>
              </v-tooltip>
              <v-tooltip top v-if="period_type==='ESTACIONAL'">
                <template v-slot:activator="{ on }">
                  <v-btn
                    :disabled="!item.importation"
                    small
                    :color="'primary'"
                    fab
                    v-on="on"
                    v-model="report_button_senasir[i]"
                    :loading="report_loading_senasir[i]"
                    @click.stop="reporteComandoSenasir(item.id,'E',i)"
                  >
                    <v-icon >mdi-file-document</v-icon> 
                  </v-btn>
                </template>
                <div>
                  <span>Reporte Pago Senasir</span>
                </div>
              </v-tooltip>
            </v-card-actions>
          </v-card>
      </v-row>
      <!-- Dialogs de importacion -->
      <v-dialog
              v-model="dialog"
              fullscreen
              hide-overlay
              transition="dialog-bottom-transition"
            >
            <v-card>
                <v-toolbar dark color="primary" >
                  <v-btn icon dark @click="dialog=close()" >
                    <v-icon>mdi-close</v-icon>
                  </v-btn>
                  <v-toolbar-title>IMPORTACION {{title}}</v-toolbar-title>
                </v-toolbar>
                <v-col cols="12" >
                  <v-row>
                    <v-col cols="2"  md="2" >
                    </v-col>
                    <v-col cols="8"  md="8" >
                      <v-col cols="12" >
                        <v-row>
                           <v-col cols="1"  md="1" >
                           </v-col>
                          <v-col cols="8"  md="8" >
                            <v-toolbar-title>
                              <center><b>INFORMACION PARA EL DESCUENTO DEL MES DE {{meses[ aux_period- 1]}} </b></center>
                            </v-toolbar-title>
                          </v-col>
                          <v-col cols="2"  md="2" >
                            <div class="text-center">
                            <v-menu
                              v-model="dialog_menu"
                              :close-on-content-click="false"
                              :nudge-width="200"
                              offset-x
                            >
                              <template v-slot:activator="{ on, attrs }">
                                <v-btn
                                  text
                                  color="indigo"
                                  dark
                                  v-bind="attrs"
                                  v-on="on"
                                >
                                  Información
                                </v-btn>
                              </template>
                              <v-card>
                                <v-list>
                                  <v-list-item>
                                    <v-list-item-content>
                                      <v-list-item-title v-show="import_export.state_affiliate =='S'">Importación Senasir</v-list-item-title>
                                       <v-list-item-title v-show="import_export.state_affiliate =='C'">Importación Comando</v-list-item-title>
                                       <v-list-item-title v-show="import_export.state_affiliate =='E'">Importación Estacional</v-list-item-title>
                                      <v-list-item-subtitle>Descripción de documento de Importacion</v-list-item-subtitle>
                                    </v-list-item-content>
                                  </v-list-item>
                                </v-list>
                                <v-divider></v-divider>
                                <div class="py-1 pl-2 ma-1">
                                    <small class="py-0 ma-0"><v-icon class="py-1 ma-0">mdi-check</v-icon>
                                      Archivo CSV </small><br>
                                    <small v-show="import_export.state_affiliate =='C'"><v-icon>mdi-check</v-icon>
                                      NOMBRE DEL ARCHIVO EJEMPLO: comando-2021-03.csv</small>
                                    <small v-show="import_export.state_affiliate =='S'"><v-icon>mdi-check</v-icon>
                                      NOMBRE DEL ARCHIVO EJEMPLO: senasir-2021-03.csv</small>
                                    <small v-show="import_export.state_affiliate =='E'"><v-icon>mdi-check</v-icon>
                                      NOMBRE DEL ARCHIVO EJEMPLO: estacional-2024-12.csv</small><br>
                                    <small class=" pl-6 ma-1"><v-icon >mdi-arrow-right-thick</v-icon>
                                      tipo-año-periodo.csv</small><br>
                                    <small><v-icon>mdi-check</v-icon>
                                      FORMATO CABECERA DEL ARCHIVO</small><br v-show="import_export.state_affiliate =='S'">
                                    <small class=" pl-6 ma-1" v-show="import_export.state_affiliate =='S'">
                                      MATRICULA:MATRICULA_DH:MONTO</small><br v-show="import_export.state_affiliate =='C'">
                                    <small class=" pl-6 ma-1" v-show="import_export.state_affiliate =='C'">
                                      CI:MONTO</small><br v-show="import_export.state_affiliate =='E'">
                                      <small class=" pl-6 ma-1" v-show="import_export.state_affiliate =='E'">
                                        CI:CI_DH:MONTO</small><br>
                                    <small><v-icon>mdi-check</v-icon>
                                      Campos del Archivo CSV</small><br v-show="import_export.state_affiliate =='S'" >
                                    <small v-show="import_export.state_affiliate =='S'" class=" pl-6 ma-1"><v-icon >mdi-arrow-right-thick</v-icon>
                                      MATRICULA: Matricula del afiliado</small><br v-show="import_export.state_affiliate =='S'">
                                     <small class=" pl-6 ma-1" v-show="import_export.state_affiliate =='S'"><v-icon >mdi-arrow-right-thick</v-icon>
                                      MATRICULA DERECHO HABIENTE: Matricula del conyugue</small><br v-show="import_export.state_affiliate =='E'" >
                                      <small v-show="import_export.state_affiliate =='E'" class=" pl-6 ma-1"><v-icon >mdi-arrow-right-thick</v-icon>
                                      Ci: CI del afiliado</small><br v-show="import_export.state_affiliate =='E'">
                                     <small class=" pl-6 ma-1" v-show="import_export.state_affiliate =='E'"><v-icon >mdi-arrow-right-thick</v-icon>
                                      CI_DH: CI del conyugue</small><br v-show="import_export.state_affiliate =='E'">
                                    <small class=" pl-6 ma-1" v-show="import_export.state_affiliate =='C'"><v-icon >mdi-arrow-right-thick</v-icon>
                                      CI: CI del afiliado</small><br>
                                     <small class=" pl-6 ma-1"><v-icon >mdi-arrow-right-thick</v-icon>
                                      MONTO : Monto de la importación</small><br>
                                </div>
                              <v-card-actions>
                              <v-spacer></v-spacer>
                              <v-btn
                                text
                                @click="dialog_menu = false"
                              >
                                Salir
                              </v-btn>
                            </v-card-actions>
                          </v-card>
                        </v-menu>
                      </div>
                    </v-col>
                  </v-row>
                  <v-progress-linear
                    color="info"
                    height="15"
                    :value="percentage"
                    striped
                  >
                    <strong>Porcentaje de Importación: {{percentage}}%</strong>
                  </v-progress-linear>
                </v-col>
                    <v-stepper v-model="e1" >
                      <v-stepper-header class=" !pa-0 ml-0" >
                        <template>
                          <v-stepper-step
                            :key="`${1}-step`"
                            :complete="e1 > 1"
                            :step="1">Subir Archivo
                          </v-stepper-step >
                          <v-divider v-if="1 !== steps" :key="1" ></v-divider>
                          <v-stepper-step
                            :key="`${2}-step`"
                            :complete="e1 > 2"
                            :step="2">Validar Datos
                          </v-stepper-step>
                          <v-divider v-if="2 !== steps" :key="2" ></v-divider>
                          <v-stepper-step
                            :key="`${3}-step`"
                            :complete="e1 > 3"
                            :step="3">Importar
                          </v-stepper-step>
                          <v-divider v-if="3 !== steps" :key="3" ></v-divider>
                        </template>
                      </v-stepper-header>
                      <v-stepper-items>
                        <v-stepper-content :key="`${1}-content`" :step="1">
                          <v-card color="grey lighten-1">
                            <v-card-text >
                              <v-card color="white">
                                <v-col cols="12" md="12">
                                  <v-row>
                                    <v-col cols="1" md="1">
                                    </v-col>
                                    <v-col cols="5" md="5">
                                      <v-select
                                        dense
                                        :items="state_affiliate"
                                        item-text="name"
                                        item-value="value"
                                        label="Estado del afiliado"
                                        disabled
                                        readonly
                                        v-model="import_export.state_affiliate"
                                      ></v-select>
                                    </v-col>
                                    <v-col cols="5" md="5">
                                      <v-file-input
                                        counter
                                        show-size
                                        truncate-length="30"
                                        outlined
                                        small-chips
                                        dense
                                        label="Cargar Archivo"
                                        v-model="import_export.file"
                                      ></v-file-input>
                                    </v-col>
                                    <v-col cols="9" md="9">
                                    </v-col>
                                    <v-col cols="2" md="2">
                                      <v-btn
                                        color="success"
                                        dark
                                        :loading="loading_uf"
                                        :disabled="import_export.file?false:true"
                                        @click.stop="uploadFilePayment()"
                                      > Subir Archivo
                                      </v-btn>
                                    </v-col>
                                  </v-row>
                                </v-col>
                              </v-card >
                            </v-card-text>
                            <v-container class="py-0">
                              <v-row>
                                <v-spacer></v-spacer> <v-spacer></v-spacer> <v-spacer></v-spacer>
                                  <v-col class="py-0">
                                    <v-btn
                                      v-show="validate_data"
                                      color="primary"
                                      @click="nextStep(1)">
                                      Siguiente
                                    </v-btn>
                                  </v-col>
                                  <!--{{contrib_codebtor}}-->
                              </v-row>
                            </v-container>
                          </v-card>
                        </v-stepper-content>
                        <v-stepper-content :key="`${2}-content`" :step="2" >
                          <v-card color="grey lighten-1">
                            <v-card-text >
                              <v-card color="white">
                                <v-row>
                                  <v-col cols="2">
                                  </v-col>
                                  <v-col cols="3">
                                    <label>
                                      Nombre del Archivo:{{fileName()}}
                                    </label>
                                  </v-col>
                                  <v-col cols="4" v-show="import_export.state_affiliate== 'S'">
                                    <label>
                                      Tipo de Importacion : SENASIR
                                    </label>
                                  </v-col>
                                  <v-col cols="4" v-show="import_export.state_affiliate== 'C'">
                                    <label>
                                      Tipo de Importacion : COMANDO
                                    </label>
                                  </v-col>
                                  <v-col cols="3">
                                    <label>
                                      Periodo:{{import_export.period_importation==null? period_show :import_export.period_importation}}
                                    </label>
                                  </v-col>
                                  <v-col cols="2">
                                  </v-col>
                                  <v-col cols="3" style="color:teal">
                                    <label>
                                      <b>{{'Datos Copiados: '+import_export.reg_copy}}</b>
                                    </label>
                                  </v-col>
                                  <v-col cols="3" style="color:teal">
                                    <label>
                                      <b>Datos Agrupados: {{import_export.reg_group==null? 0:import_export.reg_group}}</b>
                                    </label>
                                  </v-col>
                                  <v-col cols="12" >
                                    <v-progress-linear></v-progress-linear>
                                  </v-col>
                                  <v-col cols="4" >
                                  </v-col>
                                  <v-col cols="3" v-show="validate_data" >
                                    <v-btn
                                      :loading="loading_uf"
                                      color="success"
                                      @click.stop="validateFilePayment()"
                                      >Validar Datos
                                      <v-icon color="white">mdi-check</v-icon>
                                    </v-btn>
                                  </v-col>
                                  <v-col cols="4" v-show="true" >
                                    <v-tooltip top>
                                      <template v-slot:activator="{ on }">
                                        <v-btn
                                          color="info"
                                          v-on="on"
                                          @click="dialog_confirm=true"
                                        >Rehacer
                                          <v-icon color="white">mdi-eraser</v-icon>
                                        </v-btn>
                                      </template>
                                      <div>
                                        <span>Empezar de nuevo</span>
                                      </div>
                                    </v-tooltip>
                                  </v-col>
                                </v-row>
                              </v-card>
                              <v-container class="py-0">
                                <v-row>
                                <v-spacer></v-spacer><v-spacer> </v-spacer> <v-spacer></v-spacer>
                                  <v-col class="py-0">
                                    <v-btn
                                      v-show="show_import"
                                      right
                                      color="primary"
                                      @click.stop="nextStep(2)">
                                      Siguiente
                                    </v-btn>
                                  </v-col>
                                </v-row>
                              </v-container>
                            </v-card-text >
                          </v-card>
                        </v-stepper-content>
                        <v-stepper-content :key="`${3}-content`" :step="3" >
                          <v-card color="grey lighten-1">
                            <h3 class="text-uppercase text-center">Ultimo Paso la Importación</h3>
                              <v-card-text >
                                <v-card color="white">
                                  <v-row>
                                   <v-col cols="2">
                                    </v-col>
                                    <v-col cols="3">
                                      <label>
                                      Nombre del Archivo:{{fileName()}}
                                      </label>
                                    </v-col>
                                    <v-col cols="4" v-show="import_export.state_affiliate== 'S'">
                                      <label>
                                        Tipo de Importacion : SENASIR
                                      </label>
                                    </v-col>
                                    <v-col cols="4" v-show="import_export.state_affiliate== 'C'">
                                      <label>
                                        Tipo de Importacion : COMANDO
                                      </label>
                                    </v-col>
                                    <v-col cols="3">
                                      <label>
                                          Periodo:{{import_export.period_importation}}
                                      </label>
                                    </v-col>
                                    <v-col cols="2">
                                    </v-col>
                                    <v-col cols="3" style="color:teal">
                                      <label>
                                        <b>{{'Datos Copiados: '+import_export.reg_copy}}</b>
                                      </label>
                                    </v-col>
                                    <v-col cols="3" style="color:teal">
                                      <label>
                                        <b>{{'Datos Agrupados: '+import_export.reg_group}}</b>
                                      </label>
                                    </v-col>
                                    <v-col cols="12" >
                                      <v-progress-linear></v-progress-linear>
                                    </v-col>
                                    <v-col cols="3" >
                                    </v-col>
                                    <v-col cols="4" >
                                      <v-btn dark color="success" v-show="show_import" @click="dialog_confirm_import=true" >
                                        Ejecutar la Importación
                                        <v-icon color="white">mdi-check</v-icon>
                                      </v-btn>
                                    </v-col>
                                    <v-col cols="4" v-show="true" >
                                      <v-tooltip top>
                                        <template v-slot:activator="{ on }">
                                          <v-btn
                                            color="info"
                                            v-on="on"
                                            @click.stop="closePayment()"
                                          > Rehacer
                                            <v-icon>mdi-eraser</v-icon>
                                          </v-btn>
                                        </template>
                                        <div>
                                          <span>Empezar de nuevo</span>
                                        </div>
                                      </v-tooltip>
                                    </v-col>
                                  </v-row>
                                </v-card>
                              </v-card-text >
                            </v-card>
                          </v-stepper-content>
                        </v-stepper-items>
                      </v-stepper>
                    </v-col>
                    <v-col cols="6"  md="6" class="py-0" >
                    </v-col>
                  </v-row>
                </v-col>
            </v-card>
      </v-dialog>
      <v-dialog
              v-model="dialog_confirm"
              max-width="600"
            >
              <v-card>
                <v-card-title>
                  <center>¿Esta seguro que quiere rehacer el proceso de importación?</center>
                  <br>
                  <br> <small class='caption'>Al rehacer se borraran todos los datos ingresados</small>
                </v-card-title>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn
                    color="red darken-1"
                    text
                    @click="dialog_confirm=false"
                  >
                    Cancelar
                  </v-btn>
                  <v-btn
                    color="green darken-1"
                    text
                    @click="closePayment()"
                  >
                    Aceptar
                  </v-btn>
                </v-card-actions>
              </v-card>
      </v-dialog>
      <v-dialog
              v-model="dialog_confirm_import"
              max-width="500"
            >
              <v-card>
                <v-card-title>
                  Esta seguro de realizar la importación?
                </v-card-title>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn
                    color="red darken-1"
                    text
                    @click="dialog_confirm_import=false"
                  >
                    Cancelar
                  </v-btn>
                  <v-btn
                    color="green darken-1"
                    text
                    :loading="loading_import"
                    @click="importPayment()"
                  >
                    Aceptar
                  </v-btn>
                </v-card-actions>
              </v-card>
      </v-dialog>
  </v-container>
</template>
<script>
export default {
  name: "payment-ImportExport",
  data: () => ({
    e1: 1,
    steps: 6,
    dialog_menu: false,
    message: false,
    show_import:false,
    loading_import:false,
    validate_data:false,
    dialog: false,
    dialog_confirm : false,
    dialog_confirm_import:false,
    aux_period:null,
    period_show:null,
    title: null,
    import_export: {
      file: null,
      state_affiliate: null,
      cutoff_date: null,
      period_importation:null
    },
    report_loading_command:[],
    report_button_command:[],
    report_loading_senasir:[],
    report_button_senasir:[],

    meses: [ 'ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'
    ],
    actions: [
      { nameTab: "Exportación", value: "export" },
      { nameTab: "Importación", value: "import" },
    ],
    state_affiliate: [
      { name: "Activo - Comando", value: 'C' },
      { name: "Pasivo - Senasir", value: 'S' },
      { name: "Pasivo - Estacional", value: 'E' },
    ],
  period_year:null,
  mes:null,
  year:[],
  month:[],
  percentage:0,
  period_type:'COMANDO',
  //variables para el uso de los loadings
  l_index: -1,
  loading_sc: false,
  loading_ss: false,
  loading_uf: false
  }),
  watch: {
    steps (val) {
      if (this.e1 > val) {
        this.e1 = val
      }
    },
    period_type(){
        this.getMonthYear();
    }
  },
  beforeMount() {
    this.getYear();
  },
  methods: {
    nextStep (n) {
      if (n == this.steps) {
        this.e1 = 1
      }
      else {
        if(n==1)
        {
          this.percentage= this.percentage + 30
        }
        if(n==2)
        {
          this.percentage= this.percentage + 30
        }
        this.e1 = n + 1
      }
    },
    //Metodo para limpiar los imputs
    clearInputs() {
      this.import_export.file = null
      this.loading_import =false
      this.loading =false
      this.percentage=0
      this.import_export.file_name = null
      this.import_export.reg_copy = 0
      this.import_export.reg_group = 0
      this.show_import=false
    },
    //Metodo para obtener el nombre del Archivo.
    //Verificamos si el backend nos envio el nombre del archivo, caso contrario lo hacemos desde el frontend
    fileName(){
      if(this.import_export.file_name ==null){
        if(this.import_export.file!=null)
          return this.import_export.file.name
      }
      else{
        return this.import_export.file_name 
      }
    },
    //Metodo para subir el archivo
    async uploadFilePayment() {
      this.loading_uf=true;
      const formData = new FormData();
      formData.append("file", this.import_export.file);
      formData.append("state", this.import_export.state_affiliate);
      await axios({
        url: "/loan_payment/upload_file_payment",
        method: "POST",
        headers: { Accept: "application/vnd.ms-excel" },
        data: formData,
      })
        .then((response) => {
            this.validate_data=response.data.validate
           if(this.validate_data){
              this.import_export.reg_copy = response.data.message[0].count
              this.toastr.success('Datos efectivizados '+ response.data.message[0].count)
           }
           else{
            this.toastr.error(response.data.message)
           }
           this.loading_uf=false;
        })
        .catch((e) => {
          console.log(e);
          this.loading_uf=false;
        });
    },
    //Metodo para sacar el año
    async getYear() {
      try {
        this.loading = true;
        let res = await axios.get(`get_list_year`);
        this.year = res.data;
        if(this.year.length > 0)
        {
          this.period_year = this.year[this.year.length-1].year
          this.getMonthYear()
        }
      } catch (e) {
        this.dialog = false;
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    //Metodo para sacar el periodo
    Onchange(){
      if(this.period_year!=null)
      {
         this.getMonthYear()
      }
    },
    //Metodo para la lista de los periodos
    async getMonthYear() {
      try {
         let res = await axios.get(`get_list_month`,{
          params:{
            year:this.period_year,
            importation_type: this.period_type
          }
        });
        this.month = res.data
      } catch (e) {
        this.loading = false;
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    //Metodo para crear el periodo
    async savePeriod() {
      try {
         let res = await axios.post(`periods`,
            {
              importation_type: this.period_type
            }
          );
         if(res.data.message){
            this.toastr.error(res.data.message)
         }else{
            this.month.push(res.data.month)
            this.period_year=res.data.year

            this.mes= res.data.id
            this.getMonthYear()
            this.$forceUpdate();
         }
      } catch (e) {
        this.loading = false;
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    //Metodo para realizar el porcentaje la importacion de Comando
    async importacionComando(month, id){
      try {
        let res = await axios.get(`periods/${id}`)
        if(res.data.import_command){
          this.toastr.error('Este periodo ya fue Importado')
        }else{
          this.aux_period= month
          this.mes=id
          if( this.aux_period < 10){
            this.period_show= res.data.year+'-0'+this.aux_period
          }else{
            this.period_show= res.data.year+'-'+this.aux_period
          }
          this.dialog=true
          this.import_export.state_affiliate = 'C'
          this.title= 'COMANDO'
          let resp = await axios.post(`loan_payment/import_progress_bar`,{
            period_id: this.mes,
            origin: this.import_export.state_affiliate
          });
          if(resp.data.percentage > 0){
            this.percentage=resp.data.percentage
            if(resp.data.query_step_1 == true)
            {
              this.e1=2
              this.validate_data=true
            }else{
              if(resp.data.query_step_2 == true){
                this.e1=3
                this.show_import=true
              }
            }
            this.import_export.file_name = resp.data.file_name
            this.import_export.reg_copy = resp.data.reg_copy
            this.import_export.reg_group = resp.data.reg_group
            this.import_export.period_importation = resp.data.period_importation
          }else{
            this.percentage=0
            this.e1=1
          }
        }
      } catch (e) {
        this.loading = false;
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    //Metodo para realizar el porcentaje la importacion de Senasir
    async importacionSenasir(month, id){
        try {
        let res = await axios.get(`periods/${id}`)
        if(res.data.import_senasir){
          this.toastr.error('Este periodo ya fue Importado')
        }else{
          this.aux_period= month
          this.mes=id
          if( this.aux_period < 10){
            this.period_show= res.data.year+'-0'+this.aux_period
          }else{
            this.period_show= res.data.year+'-'+this.aux_period
          }
          this.dialog=true
          this.import_export.state_affiliate = 'S'
          this.title= 'SENASIR'
          let resp = await axios.post(`loan_payment/import_progress_bar`,{
            period_id: this.mes,
            origin: this.import_export.state_affiliate
          });
          if(resp.data.percentage > 0){
            this.percentage=resp.data.percentage
            if(resp.data.query_step_1 == true)
            {
              this.e1=2
              this.validate_data=true
            }else{
              if(resp.data.query_step_2 == true){
                this.e1=3
                this.show_import=true
              }
            }
            this.import_export.file_name = resp.data.file_name
            this.import_export.reg_copy = resp.data.reg_copy
            this.import_export.reg_group = resp.data.reg_group
            this.import_export.period_importation = resp.data.period_importation
          }else{
            this.percentage=0
            this.e1=1
          }
        }
      } catch (e) {
        this.loading = false;
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    //Metodo para realizar el porcentaje la importacion de Prestamos estacionales
    async importacionEstacional(month, id){
        try {
        let res = await axios.get(`periods/${id}`)
        if(res.data.import_senasir){
          this.toastr.error('Este periodo ya fue Importado')
        }else{
          this.aux_period= month
          this.mes=id
          if( this.aux_period < 10){
            this.period_show= res.data.year+'-0'+this.aux_period
          }else{
            this.period_show= res.data.year+'-'+this.aux_period
          }
          this.dialog=true
          this.import_export.state_affiliate = 'E'
          this.title= 'Estacional'
          let resp = await axios.post(`loan_payment/import_progress_bar`,{
            period_id: this.mes,
            origin: this.import_export.state_affiliate
          });
          if(resp.data.percentage > 0){
            this.percentage=resp.data.percentage
            if(resp.data.query_step_1 == true)
            {
              this.e1=2
              this.validate_data=true
            }else{
              if(resp.data.query_step_2 == true){
                this.e1=3
                this.show_import=true
              }
            }
            this.import_export.file_name = resp.data.file_name
            this.import_export.reg_copy = resp.data.reg_copy
            this.import_export.reg_group = resp.data.reg_group
            this.import_export.period_importation = resp.data.period_importation
          }else{
            this.percentage=0
            this.e1=1
          }
        }
      } catch (e) {
        this.loading = false;
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    //Metodo para realizar la validacion del archivo a importar
    async validateFilePayment(){
    try {
      this.loading_uf=true
      let res = await axios.get(`agruped_payments`,{
        params:{
        origin: this.import_export.state_affiliate,
        period: this.mes
        }
      })
       if(res.data.validated_agroup){
        this.show_import =res.data.validated_agroup
        this.import_export.reg_group = res.data.count_affilites
        this.toastr.success(res.data.message +' '+'cantidad de afiliados '+res.data.count_affilites)
      }
      else{
         this.show_import =res.data.validated_agroup

          const formData = new FormData();

         await axios({
          url: '/upload_fail_validated_group',
          method: "GET",
          responseType: "blob", // important
          headers: { Accept: "application/vnd.ms-excel" },
          //headers: { Accept: "text/plain" },
          data: formData,
          params: {
            origin: this.import_export.state_affiliate,
            period: this.mes
          }
        })
          .then((response) => {
           const url = window.URL.createObjectURL(new Blob([response.data]));
           const link = document.createElement("a");
           link.href = url;
           link.setAttribute("download", "ReporteAfiliadosNoImportados.xls");
           document.body.appendChild(link);
           link.click();
         })
           this.toastr.error(res.data.message)
      }

      } catch (e) {
        this.loading = false;
        console.log(e);
      } finally {
        this.loading = false;
        this.loading_uf=false;
      }
    },
    //Metodo para realizar la importacion
    async importPayment(){
    try {
      this.loading_import = true
      if( this.loading_import == true)
      {
        if(this.import_export.state_affiliate=='C'){
          let res = await axios.get(`create_payments_command`,{
            params:{
              period: this.mes
          }
        })
        if(res.data.importation_validated){
          if(res.status==201 || res.status == 200){
            this.dialog=false
            this.dialog_confirm_import=false
            this.toastr.success('Importado Correctamente: '+res.data.paid_by_lenders+ ' titulares y '+ res.data.paid_by_guarantors+' garantes' )
          }
        }else{
          this.dialog_confirm_import=false
          this.toastr.error(res.data.message)
        }
      }else if(this.import_export.state_affiliate=='S'){
        let res = await axios.get(`importation_payments_senasir`,{
        params:{
          period: this.mes
        }
      })
      if(res.data.importation_validated){
        if(res.status==201 || res.status == 200){
          this.dialog=false
          this.dialog_confirm_import=false
          this.toastr.success('Importado Correctamente: '+res.data.paid_by_lenders+ ' titulares y '+ res.data.paid_by_guarantors+' garantes' )
        }
      }
      else{
        this.toastr.error(res.data.message)
      }
     } else if(this.import_export.state_affiliate=='E'){
        let res = await axios.get(`importation_payments_estacional`,{
        params:{
          period: this.mes
        }
      })
      if(res.data.importation_validated){
        if(res.status==201 || res.status == 200){
          this.dialog=false
          this.dialog_confirm_import=false
          this.toastr.success('Importado Correctamente: '+res.data.paid_by_lenders+ ' titulares')
        }
      }
      else{
        this.toastr.error(res.data.message)
      }
     }
      }
        this.show_import=false
        this.validate_data=false
        this.clearInputs()
       } catch (e) {
        this.loading = false;
        console.log(e);
      } finally {
        this.loading = false;
        this.getMonthYear();
      }
    },
    //Metodo para reiniciar el proceso de importacion realizando el rollback
    async closePayment(){
        let res = await axios.get(`rollback_copy_groups_payments`,{
        params:{
          origin: this.import_export.state_affiliate,
          period: this.mes
        }
      })
      if(res.data.validated_rollback){
          this.toastr.success(res.data.message)
      }
      else{
        this.toastr.error(res.data.message)
      }
      this.clearInputs()

      this.e1=1
      this.dialog_confirm=false
      this.validate_data=false
      this.show_import=false
    },
    close()
    {
      this.clearInputs()
    },
    //Metodo para saacr el reporte de Comando y Senasir
   async reporteComandoSenasir(id, tipo, i){
    try {
      for(let j=0;j <= i; j++)
      {
        this.report_button_command.push(j)
        this.report_loading_command.push(false)
        this.report_button_senasir.push(j)
        this.report_loading_senasir.push(false)
      }
      if(tipo=='C' )
      {
        this.report_loading_command[i]=true
      }
      else{
        this.report_loading_senasir[i]=true
      }
      if(this.report_loading_command[i] ==true ||this.report_loading_senasir[i]==true ){

          const formData = new FormData();

         await axios({
          url: '/report_amortization_importation_payments',
          method: "GET",
          responseType: "blob", // important
          headers: { Accept: "application/vnd.ms-excel" },
          //headers: { Accept: "text/plain" },
          data: formData,
          params: {
            origin: tipo,
            period: id
          }
        })
          .then((response) => {
           const url = window.URL.createObjectURL(new Blob([response.data]));
           const link = document.createElement("a");
           link.href = url;
           link.setAttribute("download", "ReporteImportacion.xls");
           document.body.appendChild(link);
           link.click();

         if(response.status==201 || response.status == 200){

            this.report_button_command=[]
            this.report_loading_command=[]
            this.report_button_senasir=[]
            this.report_loading_senasir=[]
            for(let j=0;j <= i; j++)
                {
                  this.report_button_command.push(j)
                  this.report_loading_command.push(false)

                  this.report_button_senasir.push(j)
                  this.report_loading_senasir.push(false)
                }
             }
          })
        }
      } catch (e) {
        this.loading = false;
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    //Metodo para sacar la solicitud de pago de comando
    async solicitudComando(tipo, id,month){
      try {
        this.loading_sc=true;
        const formData = new FormData();

         await axios({
          url: '/report_request_institution',
          method: "GET",
          responseType: "blob", // important
          headers: { Accept: "application/vnd.ms-excel" },
          //headers: { Accept: "text/plain" },
          data: formData,
          params: {
            origin: tipo,
            period: id
          }
        })
          .then((response) => {
           const url = window.URL.createObjectURL(new Blob([response.data]));
           const link = document.createElement("a");
           link.href = url;
           link.setAttribute("download", `SolicitudComando${this.$options.filters.capitalize(month)}.xls`);
           document.body.appendChild(link);
           link.click();
        })

      }
       catch (e) {
        this.loading = false;
        console.log(e);
      } finally {
        this.loading = false;
        this.loading_sc=false;
        this.l_index=-1;
      }
    },
    //Metodo para sacar la solicitud de pago de senasir
       async solicitudSenasir(tipo, id,month){
        try {
        this.loading_ss=true;
        const formData = new FormData();

         await axios({
          url: '/report_request_institution',
          method: "GET",
          responseType: "blob", // important
          headers: { Accept: "application/vnd.ms-excel" },
          //headers: { Accept: "text/plain" },
          data: formData,
          params: {
            origin: tipo,
            period: id
          }
        })
          .then((response) => {
           const url = window.URL.createObjectURL(new Blob([response.data]));
           const link = document.createElement("a");
           link.href = url;
           link.setAttribute("download", `SolicitudSenasir${this.$options.filters.capitalize(month)}.xls`);
           document.body.appendChild(link);
           link.click();
        })
      } catch (e) {
        this.loading = false;
        console.log(e);
      } finally {
        this.loading = false;
        this.loading_ss=false;
        this.l_index=-1;
      }
    },
  },
};
</script>
<style scoped>
  .select-year {
    max-width: 100px;
    margin-bottom: -30px;
  }
  .btn-period.v-btn:not(.v-btn--round).v-size--default{
    min-width: 160px;
    height: 25px;
  }
</style>