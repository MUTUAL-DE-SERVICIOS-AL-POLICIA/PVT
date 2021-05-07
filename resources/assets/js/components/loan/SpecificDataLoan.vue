<template>
  <v-container fluid class="py-0 px-0">
    <ValidationObserver ref="observer">
      <v-form>
        <!--BOTONES CUANDO SE REALICE LA EDICIÓN-->

        <v-row justify="center" >
            <v-col cols="12" class="py-0 px-0">
              <v-container fluid class="py-0 px-6  ">
                <v-row class="py-0">
                  <v-col cols="12" class="py-0">
                    <v-tabs dark active-class="secondary">

                      <v-tab>DATOS DEL PRESTAMO</v-tab>
                        <v-tab-item>
                          <v-card flat tile class="py-0">
                            <v-card-text class="py-0">
                              <v-col cols="12" md="12" color="orange">
                                <v-row>
                                  <v-col cols="12" md="11" class="py-0">
                                    <p style="color:teal"><b>TITULAR</b></p>
                                  </v-col>
                                  <v-col cols="12" md="1" class="py-0" >
                                <div v-if="permissionSimpleSelected.includes('update-loan-calculations')" >
                                  <v-tooltip top >
                                    <template v-slot:activator="{ on }">
                                      <v-btn
                                        fab
                                        dark
                                        x-small
                                        :color="'error'"
                                        top
                                        right
                                        v-on="on"
                                        @click.stop="resetForm()"
                                        v-show="calificacion_edit"
                                      >
                                        <v-icon>mdi-close</v-icon>
                                      </v-btn>
                                    </template>
                                    <div>
                                      <span>Cancelar</span>
                                    </div>
                                  </v-tooltip>
                                  <v-tooltip top >
                                    <template v-slot:activator="{ on }">
                                      <v-btn
                                        fab
                                        dark
                                        x-small
                                        :color="calificacion_edit ? 'danger' : 'success'"
                                        top
                                        right
                                        v-on="on"
                                        @click.stop="editSimulate()"
                                      >
                                        <v-icon v-if="calificacion_edit">mdi-check</v-icon>
                                        <v-icon v-else>mdi-pencil</v-icon>
                                      </v-btn>
                                    </template>
                                    <div>
                                      <span v-if="calificacion_edit">Guardar Montos</span>
                                      <span v-else>Editar</span>
                                    </div>
                                  </v-tooltip>
                                  </div>
                                  </v-col>
                                  <v-progress-linear></v-progress-linear>
                                  <v-col cols="12" md="4" v-show="!calificacion_edit" class="pb-0">
                                    <p><b>MONTO SOLICITADO: </b>{{' '+loan.amount_approved | moneyString}} Bs.</p>
                                  </v-col>
                                  <v-col cols="12" md="4" v-show="calificacion_edit" class="pb-0" >
                                    <v-text-field
                                      dense
                                      label="MONTO SOLICITADO"
                                      v-model="loan.amount_approved"
                                      v-on:keyup.enter="simuladores()"
                                      :outlined="true"
                                    ></v-text-field>
                                  </v-col>
                                  <v-col cols="12" md="4" class="pb-0">
                                    <p><b>PROMEDIO LIQUIDO PAGABLE: </b>{{' '+ loan.lenders[0].pivot.payable_liquid_calculated | moneyString }} Bs.</p>
                                  </v-col>
                                  <v-col cols="12" md="4" class="pb-0" >
                                    <p><b>LIQUIDO PARA CALIFICACION: </b>{{loan.liquid_qualification_calculated | moneyString}} Bs.</p>
                                  </v-col>
                                  <v-col cols="12" md="4" v-show="!calificacion_edit" class="py-0">
                                    <p><b>PLAZO EN MESES:</b>{{' '+loan.loan_term}}</p>
                                  </v-col>
                                  <v-col cols="12" md="4" v-show="calificacion_edit" class="py-0" >
                                    <v-text-field
                                      dense
                                      label="PLAZO EN MESES"
                                      v-model="loan.loan_term"
                                      v-on:keyup.enter="simuladores()"
                                      :outlined="true"
                                    ></v-text-field>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0">
                                    <p><b>TOTAL BONOS:</b>{{' '+loan.lenders[0].pivot.bonus_calculated | moneyString}}</p>
                                  </v-col>
                                   <v-col cols="12" md="4" class="py-0">
                                    <p><b>INDICE DE ENDEUDAMIENTO:</b>{{' '+ parseInt(loan.indebtedness_calculated).toFixed(2)+'%'}} </p>
                                  </v-col>
                                  <v-col cols="12" md="4" v-show="calificacion_edit" class="py-0">
                                    <center>
                                      <v-btn
                                        class="py-0 text-right"
                                        color="info"
                                        rounded
                                        x-small
                                        @click="simuladores()">Calcular
                                      </v-btn>
                                    </center>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0">
                                    <p><b>CALCULO DE CUOTA: </b>{{' '+loan.estimated_quota | moneyString}} Bs.</p>
                                  </v-col>
                                  <v-col cols="12" md="12" >
                                    <div v-for="procedure_type in procedure_types" :key="procedure_type.id">
                                      <div v-if="procedure_type.name === 'Préstamo Hipotecario'">
                                        <v-progress-linear></v-progress-linear><br>
                                          <p style="color:teal"><b>CODEUDOR</b></p>
                                          <div v-for="(lenders,i) in loan.lenders" :key="i">
                                            <div  v-if="(lenders,i)>0">
                                              <p><b>PROMEDIO LIQUIDO PAGABLE:</b>{{' '+lenders.pivot.payable_liquid_calculated}}</p>
                                              <p><b>TOTAL BONOS:</b>{{' '+lenders.pivot.bonus_calculated}}</p>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </v-col>
                                  <v-progress-linear v-show="loan_refinancing.refinancing"></v-progress-linear>
                                    <v-col cols="12" md="6" class="pb-0" v-show="loan_refinancing.refinancing">
                                    <p style="color:teal"><b>DATOS DEL PRESTAMO A REFINANCIAR{{' => '+ loan_refinancing.description}}</b></p>
                                  </v-col>
                                <v-col cols="12" md="6" class="py-0" v-show="loan_refinancing.refinancing">
                                <div  v-if="permissionSimpleSelected.includes('update-refinancing-balance')">
                                  <v-tooltip top >
                                    <template v-slot:activator="{ on }">
                                      <v-btn
                                        fab
                                        dark
                                        x-small
                                        :color="'error'"
                                        top
                                        right
                                        v-on="on"
                                        @click.stop="resetForm()"
                                        v-show="cobranzas_edit"
                                      >
                                        <v-icon>mdi-close</v-icon>
                                      </v-btn>
                                    </template>
                                    <div>
                                      <span>Cancelar</span>
                                    </div>
                                  </v-tooltip>
                                  <v-tooltip top >
                                    <template v-slot:activator="{ on }">
                                      <v-btn
                                        fab
                                        dark
                                        x-small
                                        :color="cobranzas_edit ? 'danger' : 'success'"
                                        top
                                        right
                                        v-on="on"
                                        @click.stop="editRefinanciamiento()"
                                      >
                                        <v-icon v-if="cobranzas_edit">mdi-check</v-icon>
                                        <v-icon v-else>mdi-calculator</v-icon>
                                      </v-btn>
                                    </template>
                                    <div>
                                      <span v-if="cobranzas_edit">Actualizar el Saldo</span>
                                      <span v-else>Editar Saldo Refinanciamiento</span>
                                    </div>
                                  </v-tooltip>
                                  </div>
                                  </v-col>
                                  <v-progress-linear v-show="loan_refinancing.refinancing"></v-progress-linear  >
                                  <v-row v-show="loan_refinancing.refinancing">
                                  <v-col cols="12" md="4" class="py-2">
                                    <p><b>Codigo de Prestamo a Padre:</b>{{' '+loan_refinancing.code}}</p>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-2" >
                                    <p><b>Monto de Prestamo Padre:</b>{{' '+loan_refinancing.amount_approved}}</p>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-2">
                                    <p><b>Plazo de Prestamo Padre:</b>{{' '+loan_refinancing.loan_term}}</p>
                                  </v-col>
                                   <v-col cols="12" md="4" class="py-0">
                                    <p><b>Cuota de Prestamo Padre:</b>{{' '+loan_refinancing.estimated_quota}}</p>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0"  v-show="!cobranzas_edit_sismu">
                                    <p><b>Fecha de Corte :</b>{{' '+loan_refinancing.date_cut_refinancing}}</p>
                                  </v-col>
                                  <v-col cols="12" md="4"  v-show="cobranzas_edit_sismu "  class="py-0">
                                    <v-text-field
                                      dense
                                      v-model="loan_refinancing.date_cut_refinancing"
                                      label="Fecha de Corte"
                                      hint="Día/Mes/Año"
                                      type="date"
                                      :outlined="true"
                                   ></v-text-field>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0" v-show="!cobranzas_edit_sismu">
                                    <p><b>Saldo de Prestamo a Refinanciar:</b>{{' '+loan_refinancing.balance}}</p>
                                  </v-col>
                                  <v-col cols="12" md="4" v-show="cobranzas_edit_sismu " class="py-0" >
                                    <v-text-field
                                      dense
                                      label="Saldo de Prestamo a Refinanciar"
                                      v-model="loan_refinancing.balance"
                                     :outlined="true"
                                    ></v-text-field>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0" >
                                    <p class="success--text"><b>Monto Solicitado del Prestamo Nuevo:</b>{{' '+loan_refinancing.amount_approved}}</p>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0" >
                                    <p class="success--text"><b>Saldo Anterior de la Deuda:</b>{{' '+loan_refinancing.balance_parent_loan_refinancing}}</p>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0" >
                                    <p class="success--text"><b>Monto del Refinanciamiento:</b>{{' '+loan_refinancing.refinancing_balance}}</p>
                                  </v-col>
                                  </v-row>
                                  <v-progress-linear></v-progress-linear>
                                  <v-col cols="12" md="12" class="pb-0" >
                                    <p style="color:teal"><b>DATOS DEL CONTRATO</b></p>
                                  </v-col>
                                  <v-progress-linear></v-progress-linear>
                                    <v-col cols="12" md="3">
                                      <v-text-field
                                        dense
                                        v-model="loan.delivery_contract_date"
                                        label="FECHA ENTREGA DE CONTRATO"
                                        hint="Día/Mes/Año"
                                        type="date"
                                        :outlined="edit_delivery_date"
                                        :readonly="!edit_delivery_date"
                                      ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="1"  v-if="!permissionSimpleSelected.includes('registration-delivery-return-contracts')">
                                    </v-col>
                                    <v-col cols="12" md="3"  v-if="permissionSimpleSelected.includes('registration-delivery-return-contracts')">
                                      <div>
                                      <v-tooltip top>
                                        <template v-slot:activator="{ on }">
                                          <v-btn
                                            fab
                                            dark
                                            x-small
                                            :color="'error'"
                                            top
                                            right
                                            v-on="on"
                                            style="margin-right: 45px;"
                                            @click.stop="resetForm()"
                                            v-show="edit_delivery_date"
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
                                            :color="edit_delivery_date ? 'danger' : 'success'"
                                            top
                                            right
                                            v-on="on"
                                            style="margin-right: 10px;"
                                            @click.stop="editDateDelivery()"
                                          >
                                            <v-icon v-if="edit_delivery_date">mdi-check</v-icon>
                                            <v-icon v-else>mdi-pencil</v-icon>
                                          </v-btn>
                                        </template>
                                        <div>
                                          <span v-if="edit_delivery_date">Guardar Fecha Entrega</span>
                                          <span v-else>Editar Fecha Entrega</span>
                                        </div>
                                      </v-tooltip>
                                    </div>
                                    </v-col>
                                    <v-col cols="12" md="3">
                                      <v-text-field
                                        dense
                                        v-model="loan.return_contract_date"
                                        label="FECHA RECEPCION DE CONTRATO"
                                        hint="Día/Mes/Año"
                                        type="date"
                                        :outlined="edit_return_date"
                                        :readonly="!edit_return_date"
                                      ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="3" v-show="loan.delivery_contract_date != null"  v-if="permissionSimpleSelected.includes('registration-delivery-return-contracts')">
                                      <div >
                                      <v-tooltip top>
                                        <template v-slot:activator="{ on }">
                                          <v-btn
                                            fab
                                            dark
                                            x-small
                                            :color="'error'"
                                            top
                                            right
                                            v-on="on"
                                            style="margin-right: 45px;"
                                            @click.stop="resetForm()"
                                            v-show="edit_return_date"
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
                                            :color="edit_return_date ? 'danger' : 'success'"
                                            top
                                            right
                                            v-on="on"
                                            style="margin-right: 10px;"
                                            @click.stop="editDateReturn()"
                                          >
                                            <v-icon v-if="edit_return_date">mdi-check</v-icon>
                                            <v-icon v-else>mdi-pencil</v-icon>
                                          </v-btn>
                                        </template>
                                        <div>
                                          <span v-if="edit_return_date">Guardar Fecha Recepcion</span>
                                          <span v-else>Editar Fecha Recepcion</span>
                                        </div>
                                      </v-tooltip>
                                    </div>
                                  </v-col>
                              </v-row>
                            </v-col>
                          </v-card-text>
                        </v-card>
                      </v-tab-item>

                    <v-tab>GARANTIA</v-tab>
                      <v-tab-item >
                        <v-card flat tile>
                          <v-card-text class="pa-0 py-0">
                            <v-col cols="12" class="mb-0 py-0">
                              <v-col cols="12" md="12" class="mb-0 py-0">
                                <v-card-text class="pa-0 mb-0">
                                  <div v-for="procedure_type in procedure_types" :key="procedure_type.id" class="pa-0 py-0" >
                                    <ul style="list-style: none" class="pa-0" v-if="procedure_type.name == 'Préstamo a Largo Plazo' || procedure_type.name == 'Préstamo a Corto Plazo'">
                                      <li v-for="guarantor in loan.guarantors" :key="guarantor.id">
                                        <v-col cols="12" md="12" class="pa-0">
                                          <v-row class="pa-2">
                                            <v-col cols="12" md="12" class="py-0">
                                              <p style="color:teal"><b>GARANTE </b></p>
                                            </v-col>
                                            <v-progress-linear></v-progress-linear><br>
                                            <v-col cols="12" md="3">
                                              <p><b>NOMBRE:</b> {{$options.filters.fullName(guarantor, true)}}</p>
                                            </v-col>
                                            <v-col cols="12" md="3">
                                              <p><b>CÉDULA DE IDENTIDAD:</b> {{guarantor.identity_card +" "+ identityCardExt(guarantor.city_identity_card_id) }}</p>
                                            </v-col>
                                            <v-col cols="12" md="3">
                                              <p><b>TELEFONO:</b> {{guarantor.cell_phone_number}}</p>
                                            </v-col>
                                            <v-col cols="12" md="3">
                                              <p><b>PORCENTAJE DE PAGO:</b> {{parseInt(guarantor.pivot.payment_percentage).toFixed(2) }} %</p>
                                            </v-col>
                                             <v-col cols="12" md="3">
                                              <p><b>LIQUIDO PARA CALIFICACION:</b> {{guarantor.pivot.payable_liquid_calculated | moneyString}}</p>
                                            </v-col>
                                            <v-col cols="12" md="3">
                                              <p><b>PROMEDIO DE BONOS:</b> {{guarantor.pivot.bonus_calculated| moneyString }}</p>
                                            </v-col>
                                            <v-col cols="12" md="3">
                                              <p><b>LIQUIDO PARA CALIFICACION CALCULADO:</b> {{guarantor.pivot.liquid_qualification_calculated | moneyString}}</p>
                                            </v-col>
                                            <v-col cols="12" md="3">
                                              <p><b>INDICE DE ENDEUDAMIENTO CALCULADO:</b> {{ parseInt(guarantor.pivot.indebtedness_calculated).toFixed(2) }} %</p>
                                            </v-col>
                                          </v-row>
                                        </v-col>
                                      </li>
                                      <br>
                                      <p v-if="loan.guarantors.length==0" style="color:teal"><b> NO TIENE GARANTES </b></p>
                                    </ul>
                                    <v-col cols="12" md="12" v-if="procedure_type.name == 'Préstamo Hipotecario'">
                                      <p style="color:teal"><b>GARANTIA HIPOTECARIA </b></p>
                                      <v-tooltip >
                                        <template v-slot:activator="{ on }">
                                          <v-btn
                                            fab
                                            dark
                                            x-small
                                            :color="'error'"
                                            top
                                            right
                                            absolute
                                            v-on="on"
                                            style="margin-right: 45px; "
                                            @click.stop="resetForm()"
                                            v-show="editable1"
                                          >
                                            <v-icon>mdi-close</v-icon>
                                          </v-btn>
                                        </template>
                                        <div>
                                          <span>Cancelar</span>
                                        </div>
                                      </v-tooltip>
                                      <v-tooltip top  v-if="permissionSimpleSelected.includes('update-warranty-hipotecary')">
                                        <template v-slot:activator="{ on }">
                                          <v-btn
                                            fab
                                            dark
                                            x-small
                                            :color="editable1 ? 'danger' : 'success'"
                                            top
                                            right
                                            absolute
                                            v-on="on"
                                            style="margin-right: -9px;"
                                            @click.stop="editLoanHipotecaryProperti()"
                                          >
                                            <v-icon v-if="editable1">mdi-check</v-icon>
                                            <v-icon v-else>mdi-pencil</v-icon>
                                          </v-btn>
                                        </template>
                                        <div>
                                          <span v-if="editable1">Guardar</span>
                                          <span v-else>Editar</span>
                                        </div>
                                      </v-tooltip>
                                      <v-row>
                                        <v-progress-linear></v-progress-linear><br>
                                        <v-col cols="12" md="4">
                                        <v-select
                                          dense
                                          :outlined="editable1"
                                          :readonly="!editable1"
                                          :items="city"
                                          item-text="name"
                                          item-value="id"
                                          label="CIUDAD"
                                          v-model="loan_properties.real_city_id"
                                        ></v-select>
                                      </v-col>
                                      <v-col cols="12" md="4">
                                        <v-text-field
                                          :outlined="editable1"
                                          :readonly="!editable1"
                                          :label="'UBICACION'"
                                          dense
                                          v-model="loan_properties.location"
                                        ></v-text-field>
                                      </v-col>
                                      <v-col cols="12" md="4">
                                        <v-text-field
                                          :outlined="editable1"
                                          :readonly="!editable1"
                                          :label="'NUMERO DE LOTE'"
                                          dense
                                          v-model="loan_properties.land_lot_number"
                                        ></v-text-field>
                                      </v-col>
                                      <v-col cols="12" md="1">
                                        <v-text-field
                                          :outlined="editable1"
                                          :readonly="!editable1"
                                          :label="'SUPERFICIE'"
                                          dense
                                          v-model="loan_properties.surface"
                                        ></v-text-field>
                                      </v-col>
                                      <v-col cols="12" md="3">
                                      <v-select
                                        :outlined="editable1"
                                        :readonly="!editable1"
                                        dense
                                        :items="items_measurement"
                                        item-text="name"
                                        item-value="value"
                                        label="Unidad de medida"
                                        v-model="loan_properties.measurement"
                                      ></v-select>
                                      </v-col>
                                      <v-col cols="12" md="4">
                                        <v-text-field
                                          :outlined="editable1"
                                          :readonly="!editable1"
                                          :label="'CODIGO CATASTRAL'"
                                          dense
                                          v-model="loan_properties.cadastral_code"
                                        ></v-text-field>
                                      </v-col>
                                      <v-col cols="12" md="4">
                                        <v-text-field
                                          :outlined="editable1"
                                          :readonly="!editable1"
                                          :label="'NRO MATRICULA'"
                                          dense
                                          v-model="loan_properties.registration_number"
                                        ></v-text-field>
                                      </v-col>
                                      <v-col cols="12" md="4">
                                        <v-text-field
                                          :outlined="editable1"
                                          :readonly="!editable1"
                                          :label="'NRO FOLIO REAL'"
                                          dense
                                          v-model="loan_properties.real_folio_number"
                                        ></v-text-field>
                                      </v-col>
                                      <v-col cols="12" md="4">
                                        <p><b>VNR: </b>{{ loan_properties.net_realizable_value}} </p>
                                      </v-col>
                                       <v-col cols="12" md="6">
                                        <v-text-field
                                          :outlined="editable1 && permissionSimpleSelected.includes('update-values-commercial-rescue')"
                                          :readonly="!editable1  && !permissionSimpleSelected.includes('update-values-commercial-rescue')"
                                          :label="'VALOR COMERCIAL'"
                                          dense
                                          v-model="loan_properties.commercial_value"
                                        ></v-text-field>
                                      </v-col>
                                       <v-col cols="12" md="6">
                                        <v-text-field
                                          :outlined="editable1 && permissionSimpleSelected.includes('update-values-commercial-rescue')"
                                          :readonly="!editable1 && !permissionSimpleSelected.includes('update-values-commercial-rescue')"
                                          :label="'RESCATE HIPOTECARIO'"
                                          dense
                                          v-model="loan_properties.rescue_value"
                                        ></v-text-field>
                                      </v-col>
                                    </v-row>
                                  </v-col>
                                  <ul style="list-style: none" class="pa-0 py-4" v-if="procedure_type.name == 'Préstamo Anticipo'">
                                    <p style="color:teal"> <b>NO TIENE GARANTES</b></p>
                                  </ul>
                                </div>
                              </v-card-text>
                            </v-col>
                          </v-col>
                          </v-card-text>
                        </v-card>
                      </v-tab-item>

                      <v-tab>DATOS PERSONA DE REFERENCIA</v-tab>
                        <v-tab-item >
                          <v-card flat tile>
                            <v-card-text>
                              <p style="color:teal" v-if="loan.personal_references.length>0"><b>PERSONA DE REFERENCIA </b></p>
                              <v-progress-linear v-if="loan.personal_references.length>0"></v-progress-linear><br>
                              <v-data-table
                                v-if="loan.personal_references.length>0"
                                :headers="headers"
                                :items="loan.personal_references"
                                >
                                <template v-slot:top>
                                  <v-dialog v-model="dialog" max-width="500px" >
                                    <v-card>
                                      <v-card-title>
                                        <span style="color:teal" class="headline">EDITAR PERSONA DE REFERENCIA</span>
                                      </v-card-title>
                                        <v-progress-linear></v-progress-linear>
                                        <v-card-text class="py-0">
                                          <v-container class="py-0">
                                            <v-row>
                                              <v-col cols="12" sm="6" md="4">
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem.first_name"
                                                  label="Primer Nombre"
                                                ></v-text-field>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="4" >
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem.second_name"
                                                  label="Segundo Nombre"
                                                ></v-text-field>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="4" >
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem.last_name"
                                                  label="Primer Apellido"
                                                ></v-text-field>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="4">
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem.mothers_last_name"
                                                  label="Segundo Apellido"
                                                ></v-text-field>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="4" >
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem.phone_number"
                                                  label="Telefono"
                                                ></v-text-field>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="4" >
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem.cell_phone_number"
                                                  label="Celular"
                                                ></v-text-field>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="12">
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem.address"
                                                  label="Direccion"
                                                ></v-text-field>
                                              </v-col>
                                            </v-row>
                                          </v-container>
                                        </v-card-text>
                                        <v-card-actions class="py-0">
                                          <v-spacer></v-spacer>
                                          <v-btn
                                            color="red"
                                            text
                                            @click="close"
                                          >
                                            Cancelar
                                          </v-btn>
                                          <v-btn
                                            color="success"
                                            text
                                            @click="guardar()"
                                          >
                                            Guardar
                                          </v-btn>
                                        </v-card-actions>
                                      </v-card>
                                    </v-dialog>
                                </template>
                                <template v-slot:[`item.actions`]="{ item }" v-if="permissionSimpleSelected.includes('update-reference-cosigner')">
                                  <v-icon
                                    small
                                    class="mr-2"
                                    @click="editItem(item)"
                                  >
                                    mdi-pencil
                                  </v-icon>
                                </template>
                              </v-data-table>
                              <p v-if="loan.personal_references.length==0" style="color:teal"> <b>NO TIENE PERSONA DE REFERENCIA</b></p>
                            </v-card-text>
                          </v-card>
                        </v-tab-item>

                        <v-tab>DATOS CODEUDOR</v-tab>
                          <v-tab-item >
                            <v-card flat tile>
                              <v-card-text>
                              <p style="color:teal" v-if="loan.cosigners.length>0"><b>CODEUDOR NO AFILIADO</b></p>
                              <v-progress-linear v-if="loan.cosigners.length>0"></v-progress-linear><br>
                              <v-data-table
                                v-if="loan.cosigners.length>0"
                                :headers="headers"
                                :items="loan.cosigners"
                                >
                                <template v-slot:top>
                                  <v-dialog v-model="dialog1" max-width="500px" >
                                    <v-card>
                                      <v-card-title>
                                        <span style="color:teal" class="headline">EDITAR CODEUDOR</span>
                                      </v-card-title>
                                       <v-progress-linear></v-progress-linear>
                                        <v-card-text class="py-0">
                                          <v-container class="py-0">
                                            <v-row >
                                              <v-col cols="12" sm="6" md="4">
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem1.first_name"
                                                  label="Primer Nombre"
                                                ></v-text-field>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="4" >
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem1.second_name"
                                                  label="Segundo Nombre"
                                                ></v-text-field>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="4" >
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem1.last_name"
                                                  label="Primer Apellido"
                                                ></v-text-field>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="4">
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem1.mothers_last_name"
                                                  label="Segundo Apellido"
                                                ></v-text-field>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="4" >
                                                <v-select
                                                  v-model="editedItem1.city_identity_card_id"
                                                  dense
                                                  :items="city"
                                                  item-text="name"
                                                  item-value="id"
                                                  label="Ciudad de Expedición"
                                                ></v-select>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="4" >
                                                <v-select
                                                  dense
                                                  :items="genders"
                                                  item-text="name"
                                                  item-value="value"
                                                  v-model="editedItem1.gender"
                                                  label="Género"
                                                ></v-select>
                                              </v-col>
                                               <v-col cols="12" sm="6" md="4" >
                                                  <v-select
                                                    dense
                                                    :items="civil_statuses"
                                                    item-text="name"
                                                    item-value="value"
                                                    label="Estado civil"
                                                    v-model="editedItem1.civil_status"
                                                  ></v-select>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="4" >
                                                <v-select
                                                  v-model="editedItem1.city_birth_id"
                                                  dense
                                                  :items="city"
                                                  item-text="name"
                                                  item-value="id"
                                                  label="Ciudad de Nacimiento"
                                                ></v-select>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="4" >
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem1.phone_number"
                                                  label="Telefono"
                                                ></v-text-field>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="4" >
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem1.cell_phone_number"
                                                  label="Celular"
                                                ></v-text-field>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="8">
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem1.address"
                                                  label="Direccion"
                                                ></v-text-field>
                                              </v-col>
                                            </v-row>
                                          </v-container>
                                        </v-card-text>
                                        <v-card-actions>
                                          <v-spacer></v-spacer>
                                          <v-btn
                                            color="red"
                                            text
                                            @click="closeCodeptor"
                                          >
                                            Cancelar
                                          </v-btn>
                                          <v-btn
                                            color="success"
                                            text
                                            @click="guardarCodeptor()"
                                          >
                                            Guardar
                                          </v-btn>
                                        </v-card-actions>
                                      </v-card>
                                    </v-dialog>
                                </template>
                                <template v-slot:[`item.actions`]="{ item }" v-if="permissionSimpleSelected.includes('update-reference-cosigner')">
                                  <v-icon
                                    small
                                    class="mr-2"
                                    @click="editItem1(item)"
                                  >
                                    mdi-pencil
                                  </v-icon>
                                </template>
                              </v-data-table>
                             <p v-if="loan.cosigners.length==0" style="color:teal"> <b>NO TIENE CODEUDORES</b></p>
                            </v-card-text>
                            </v-card>
                          </v-tab-item>

                          <v-tab>DESEMBOLSO</v-tab>
                            <v-tab-item >
                              <v-card flat tile>
                                <v-card-text>
                                  <v-col cols="12" class="mb-0">
                                    <p style="color:teal"> <b>DATOS DE DESEMBOLSO</b></p>
                                     <div v-if="permissionSimpleSelected.includes('change-disbursement-date') || permissionSimpleSelected.includes('update-accounting-voucher')">
                                        <v-tooltip top>
                                          <template v-slot:activator="{ on }">
                                            <v-btn
                                              fab
                                              dark
                                              x-small
                                              :color="'error'"
                                              top
                                              right
                                              absolute
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
                                              :color="editable ? 'danger' : 'success'"
                                              top
                                              right
                                              absolute
                                              v-on="on"
                                              style="margin-right: -9px;"
                                              @click.stop="editLoan()"
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
                                      </div>
                                    <v-row>
                                      <v-progress-linear></v-progress-linear><br>
                                      <v-col cols="12" md="4">
                                        <p><b>TIPO DE DESEMBOLSO:</b> {{loan.payment_type.name}}</p>
                                      </v-col>
                                      <v-col cols="12" md="3" v-show="loan.payment_type.name=='Depósito Bancario'">
                                        <p><b>ENTIDAD FINANCIERA:</b>{{' '+cuenta}}</p>
                                      </v-col>
                                      <v-col cols="12" md="3" v-show="loan.payment_type.name=='Depósito Bancario'">
                                        <p><b>NUMERO DE CUENTA:</b>{{' '+loan.lenders[0].account_number}}</p>
                                      </v-col>
                                      <v-col cols="12" md="3" v-show="loan.payment_type.name=='Depósito Bancario'">
                                        <p><b>CUENTA SIGEP:</b> {{' '+loan.lenders[0].sigep_status}}</p>
                                      </v-col>
                                    
                                      <!--v-col cols="12" md="4">
                                        <v-select
                                          dense
                                          :outlined="permissionSimpleSelected.includes('disbursement-loan') ? editable : false"
                                          :readonly="permissionSimpleSelected.includes('disbursement-loan') ? !editable : true"
                                          :items="payment_types"
                                          item-text="name"
                                          item-value="id"
                                          label="TIPO"
                                          @change="desembolso()"
                                          v-model="loan.payment_type_id"
                                        ></v-select>
                                      </!--v-col-->
                                      <!--v-col cols="12" md="4">
                                        <div v-if="loan.payment_type_id=='1'"  class="py-0">
                                          <v-text-field
                                            dense
                                            :outlined="permissionSimpleSelected.includes('disbursement-loan') ? editable : false"
                                            :readonly="permissionSimpleSelected.includes('disbursement-loan') ? !editable : true"
                                            :label="'NRO DE DEPOSITO'"
                                            v-model="loan.number_payment_type"
                                          ></v-text-field>
                                        </div>
                                        <div v-if="loan.payment_type_id!='1'">
                                          <v-text-field
                                            dense
                                            :outlined="permissionSimpleSelected.includes('disbursement-loan') ? editable : false"
                                            :readonly="permissionSimpleSelected.includes('disbursement-loan') ? !editable : true"
                                            :label="loan.payment_type_id=='2'? 'NRO DE CHEQUE':loan.payment_type_id=='3'?'NRO DE RECIBO':'OTRO'"
                                            v-model="loan.number_payment_type"
                                          ></v-text-field>
                                        </div>
                                      </-v-col-->
                                      <!--<v-col cols="12" md="4">
                                        <div class="py-0">
                                          <v-text-field
                                            dense
                                            :outlined="editable"
                                            :readonly="!editable"
                                            :label="'CÓDIGO DE CERTIFICACIÓN PRESUPUESTARIA'"
                                             v-model="loan.num_budget_certification"
                                          ></v-text-field>
                                        </div>
                                      </v-col>-->
                                       <v-col cols="12" md="4">
                                        <div class="py-0">
                                          <v-text-field
                                            dense
                                            :outlined="permissionSimpleSelected.includes('update-accounting-voucher') ? editable : false"
                                            :readonly="permissionSimpleSelected.includes('update-accounting-voucher') ? !editable : true"
                                            :label="'CERTIFICACIÓN PRESUPUESTARIA CONTABLE'"
                                             v-model="loan.num_accounting_voucher"
                                          ></v-text-field>
                                        </div>
                                      </v-col>
                                         <v-col cols="12" md="4">
                                        <v-text-field
                                          dense
                                          v-model=" loan.disbursement_date"
                                          label="FECHA DE DESEMBOLSO"
                                          hint="Día/Mes/Año"
                                          type="date"
                                         :outlined="permissionSimpleSelected.includes('change-disbursement-date') ? editable : false"
                                          :readonly="permissionSimpleSelected.includes('change-disbursement-date') ? !editable : true"
                                        ></v-text-field>
                                      </v-col>
                                    </v-row>
                                  </v-col>
                                </v-card-text>
                              </v-card>
                            </v-tab-item>
                          </v-tabs>
                        </v-col>
                </v-row>
              </v-container>
            </v-col>
          </v-row>
        <!--/v-card-->
      </v-form>
    </ValidationObserver>
  </v-container>
</template>
<script>
export default {
  name: "specific-data-loan",
  props: {
    loan_refinancing: {
      type: Object,
      required: true
    },
    loan: {
      type: Object,
      required: true
    },
    loan_properties: {
      type: Object,
      required: true
    },
    procedure_types: {
      type: Object,
      required: true
    },
    /*validate:{
      type: Object,
      required: false
    }*/
  },
   data: () => ({
     civil_statuses: [
      { name: "Soltero", value: "S" },
      { name: "Casado", value: "C" },
      { name: "Viudo", value: "V" },
      { name: "Divorciado", value: "D" }
    ],
    items_measurement: [
      { name: "Metros cuadrados", value: "METROS CUADRADOS" },
      { name: "Hectáreas", value: "HECTÁREAS" }
    ],
    genders: [
      {
        name: "Femenino",
        value: "F"
      },
      {
        name: "Masculino",
        value: "M"
      }
    ],
      dialog: false,
      dialog1: false,
      calificacion_edit:false,
      cobranzas_edit:false,
      cobranzas_edit_sismu:false,
      edit_return_date : false,
      edit_delivery_date : false,
      editedIndex: -1,
      editedItem: {},
      defaultItem: {},
      editedItem1: {},
      defaultItem1: {},
      headers: [
        {
          text: 'PRIMER NOMBRE',
          align: 'start',
          sortable: false,
          value: 'first_name',
        },
        { text: 'SEGUNDO NOMBRE',  value: 'second_name' },
        { text: 'PRIMER APELLIDO ', value: 'last_name' },
        { text: 'SEGUNDO APELLIDO ', value: 'mothers_last_name' },
        { text: 'TELEFONO', value: 'phone_number' },
        { text: 'CELULAR', value: 'cell_phone_number' },
        { text: 'DIRECCION ', value: 'address' },
        {
      text: "Actions",
      value: "actions",
      sortable: false
    }
      ],

    editable1: false,
    editable: false,
    reload: false,
    payment_types:[],
    city: [],
    entity: [],
    entities:null,
  }),
  beforeMount(){
    this.getPaymentTypes()
    this.getCity()
    this.getEntity()
  },
  computed: {
      //Metodo para obtener Permisos por rol
      permissionSimpleSelected () {
        return this.$store.getters.permissionSimpleSelected
      },
      cuenta() {
       for (this.i = 0; this.i< this.entity.length; this.i++) {
        if(this.loan.lenders[0].financial_entity_id==this.entity[this.i].id)
        {
          this.entities= this.entity[this.i].name
        }
      }
      return this.entities
    }
  },
  watch: {
      dialog (val) {
        val || this.close()
      },
    },
  methods:{
    async guardar(){
      let res = await axios.patch(`personal_reference/${this.editedItem.id}`, {
        first_name:this.editedItem.first_name,
        second_name:this.editedItem.second_name,
        last_name:this.editedItem.last_name,
        mothers_last_name:this.editedItem.mothers_last_name,
        phone_number:this.editedItem.phone_number,
        cell_phone_number:this.editedItem.cell_phone_number,
        address:this.editedItem.address})
        this.toastr.success('Se registró correctamente.')
    this.close()
    this.$forceUpdate()
},
  async guardarCodeptor(){
      let res = await axios.patch(`personal_reference/${this.editedItem1.id}`, {
        first_name:this.editedItem1.first_name,
        second_name:this.editedItem1.second_name,
        last_name:this.editedItem1.last_name,
        mothers_last_name:this.editedItem1.mothers_last_name,

        city_identity_card_id:this.editedItem1.city_identity_card_id,
        gender:this.editedItem1.gender,
        civil_status:this.editedItem1.civil_status,
        city_birth_id:this.editedItem1.city_birth_id,

        phone_number:this.editedItem1.phone_number,
        cell_phone_number:this.editedItem1.cell_phone_number,
        cell_phone_number:this.editedItem1.cell_phone_number,
        address:this.editedItem1.address})
        this.toastr.success('Se registró correctamente.')
    this.closeCodeptor()
    this.$forceUpdate()
},

      editItem1 (item) {
        //this.editedIndex = this.loan.personal_references.indexOf(item)
        this.editedItem1 =  item
        console.log('edit')
        console.log(this.editedItem1)
        this.dialog1 = true
      },
      editItem (item) {
        //this.editedIndex = this.loan.personal_references.indexOf(item)
        this.editedItem =  item
        console.log('edit')
        console.log(this.editedItem)
        this.dialog = true
      },
      closeCodeptor() {
        this.dialog1 = false
        this.$nextTick(() => {
          this.editedItem1 = Object.assign({}, this.defaultItem1)
          this.editedIndex = -1
        })
      },
      close () {
        this.dialog = false
        this.$nextTick(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        })
      },
    desembolso () {
      if(this.loan.payment_type_id=='1'){
      this.loan.number_payment_type = this.loan.lenders[0].account_number;
      }else{
      this.loan.number_payment_type = null
    }
    },
    async getEntity() {
      try {
        this.loading = true
        let res = await axios.get(`financial_entity`)
        this.entity = res.data
        console.log("ciudad "+ this.entity)

      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getCity() {
      try {
        this.loading = true
        let res = await axios.get(`city`)
        this.city = res.data
        console.log("ciudad "+ this.city)

      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    identityCardExt(id){
      let ext
      if(id != null){
        for(let i=0; i<this.city.length;i++){
          if(this.city[i].id == id){
            ext = this.city[i].first_shortened
          }  
        }
      return ext
      }else{
        return ''
      }
    },
    resetForm() {
      this.editable1 = false
      this.editable = false
      this.calificacion_edit = false
      this.cobranzas_edit=false
      this.cobranzas_edit_sismu=false
      this.edit_return_date = false
      this.edit_delivery_date = false
      this.reload = true
      if(this.loan_refinancing.type_sismu==true){
        this.loan_refinancing.balance= this.loan.balance_parent_loan_refinancing
      }else{
        this.loan_refinancing.balance= this.loan.balance_parent_loan_refinancing
      }
      this.loan_refinancing.date_cut_refinancing= this.loan.date_cut_refinancing
      this.$nextTick(() => {
      this.reload = false
      })
    },
     async simuladores() {
      try {
        if(this.loan.amount_approved_before >= this.loan.amount_approved)
        {
          let res = await axios.post(`simulator`, {
            procedure_modality_id:this.loan.procedure_modality_id,
            amount_requested: this.loan.amount_approved,
            months_term:  this.loan.loan_term,
            guarantor: false,
            liquid_qualification_calculated_lender: 0,
            liquid_calculated:[
              {
                affiliate_id: this.loan.lenders[0].id,
                liquid_qualification_calculated: this.loan.lenders[0].pivot.liquid_qualification_calculated
              }
            ]
        })
            this.loan.amount_approved = res.data.amount_requested
            this.loan.loan_term = res.data.months_term
            this.loan.indebtedness_calculated = res.data.indebtedness_calculated_total
            this.loan.estimated_quota = res.data.quota_calculated_estimated_total
        }
        else{
          this.loan.amount_approved = this.loan.amount_approved_before
          this.loan.loan_term = this.loan.loan_term_before
          this.toastr.error("El Monto Solicitado sobrepasa al monto anterior")
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
      async editDateDelivery(){
      try {
        if (!this.edit_delivery_date) {
          this.edit_delivery_date = true
          console.log('entro al grabar por verdadero :)')
        } else {
          console.log('entro al grabar por falso :)')
          //Edit data delivery
            let res = await axios.patch(`loan/${this.loan.id}`, {
            delivery_contract_date:this.loan.delivery_contract_date,
           })
            this.toastr.success('Se registró correctamente.')
            this.edit_delivery_date = false
         }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
      async editDateReturn(){
      try {
        if (!this.edit_return_date) {
          this.edit_return_date = true
          console.log('entro al grabar por verdadero :)')
        } else {
          console.log('entro al grabar por falso :)')
          //Edit data return
            let res = await axios.patch(`loan/${this.loan.id}`, {
              return_contract_date: this.loan.return_contract_date
          })
            this.toastr.success('Se registró correctamente.')
            this.edit_return_date = false
         }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async editLoan(){
      try {
        if (!this.editable) {
          this.editable = true
          console.log('entro al grabar por verdadero :)')
        } else {
          console.log('entro al grabar por falso :)')
          //Edit desembolso
            if(this.loan.disbursement_date=='Fecha invalida'){
              let res = await axios.patch(`loan/${this.loan.id}`, {
               num_accounting_voucher: this.loan.num_accounting_voucher
            })
          }else{
          let res = await axios.patch(`loan/${this.loan.id}`, {
            // payment_type_id: this.loan.payment_type_id,
            // number_payment_type: this.loan.number_payment_type,
            disbursement_date:this.loan.disbursement_date,
            date_signal:false,
          })
        }
            this.toastr.success('Se registró correctamente.')
            this.editable = false
         }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async editRefinanciamiento(){
      try {
        if (!this.cobranzas_edit) {
          this.cobranzas_edit = true
          if(this.loan_refinancing.type_sismu){
            this.cobranzas_edit_sismu= true
          }else{
            this.cobranzas_edit_sismu= false
          }
        } else {
         //Edit refinancing
            if(this.loan_refinancing.type_sismu==true){
                 let res1 = await axios.patch(`loan/${this.loan.id}/sismu`, {
                 data_loan:[{
                    date_cut_refinancing: this.loan_refinancing.date_cut_refinancing,
                    balance : this.loan_refinancing.balance
                  }
                 ]
               })
                let res = await axios.patch(`loan/${this.loan.id}/update_refinancing_balance`)
                this.loan_refinancing.refinancing_balance= res.data.refinancing_balance
                this.loan_refinancing.balance_parent_loan_refinancing= res.data.balance_parent_loan_refinancing
      
            }else{
               this.cobranzas_edit_sismu=false
              let res = await axios.patch(`loan/${this.loan.id}/update_refinancing_balance`)
              this.loan_refinancing.refinancing_balance= res.data.refinancing_balance
              this.loan_refinancing.balance_parent_loan_refinancing= res.data.balance_parent_loan_refinancing
      
            }
            this.toastr.success('Se Actualizó Correctamente.')
            this.cobranzas_edit = false
            this.cobranzas_edit_sismu= false
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }

    },
       async editSimulate(){
      try {
        if (!this.calificacion_edit) {
          this.calificacion_edit = true
          console.log('entro al grabar por verdadero :)')
        } else {
          console.log('entro al grabar por falso :)')
         //Edit monto y plazo
            let res = await axios.patch(`edit_loan/${this.loan.id}/qualification`, {
            amount_approved: this.loan.amount_approved,
            loan_term: this.loan.loan_term
          })
            this.toastr.success('Se registró correctamente.')
            this.calificacion_edit = false
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
      async editLoanHipotecaryProperti(){
      try {
        if (!this.editable1) {
          this.editable1 = true
         } else {
           //Edita propiedad hipotecaria
          let res = await axios.patch(`loan_property/${this.loan_properties.id}`, {
            location:this.loan_properties.location,
            land_lot_number:this.loan_properties.land_lot_number,
            real_city_id:this.loan_properties.real_city_id,
            surface:this.loan_properties.surface,
            measurement:this.loan_properties.measurement,
            cadastral_code:this.loan_properties.cadastral_code,
            registration_number:this.loan_properties.registration_number,
            real_folio_number:this.loan_properties.real_folio_number,
            commercial_value :this.loan_properties.commercial_value,
            rescue_value :this.loan_properties.rescue_value
          })
            this.toastr.success('Se registró correctamente.')
            this.editable1 = false
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
     async getPaymentTypes() {
      try {
        this.loading = true
        let res = await axios.get(`payment_type`)
        this.payment_types = res.data
        console.log(this.payment_types+'este es el tipo de desembolso');
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
  }
}
</script>