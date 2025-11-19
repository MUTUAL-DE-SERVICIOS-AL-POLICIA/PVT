<template>
  <v-container fluid class="py-0 px-0">
    <ValidationObserver ref="observer">
      <v-form>
        <!--BOTONES CUANDO SE REALICE LA EDICIÓN-->

        <v-row justify="center" >
            <v-col cols="12" class="px-0">
              <v-container fluid class="pb-0 px-6 ">
                <v-row class="py-0">
                  <v-col cols="12" class="py-0">
                    <v-tabs dark active-class="secondary">

                      <v-tab>DATOS DEL PRÉSTAMO</v-tab>
                        <v-tab-item>
                          <v-card flat tile class="py-0">
                            <v-card-text class="py-0">
                              <v-col cols="12" md="12" color="orange">
                                <v-row>
                                  <v-col cols="12" md="11" class="py-0">
                                    <p style="color:teal"><b>TITULAR</b></p>
                                  </v-col>
                                  <v-col cols="12" md="1" class="py-0" >
                                    <EditCancelButton
                                      :editing="qualification_edit"
                                      :permission="permissionSimpleSelected.includes('update-loan-calculations')"
                                      :onCancel="resetForm"
                                      :onEdit="editSimulator"
                                      iconEditing="mdi-check"
                                      iconCancel="mdi-close"
                                      iconDefault="mdi-pencil"
                                      textEditing="Guardar Montos"
                                      textCancel="Cancelar"
                                      textDefault="Editar"
                                      :cancelStyle="{ }"
                                      :editStyle="{ }"
                                    />
                                  </v-col>
                                  <v-progress-linear color="blue-grey lighten-3"></v-progress-linear>
                                  <v-col cols="12" md="4" v-show="!qualification_edit" class="pb-0">
                                    <p><b>MONTO SOLICITADO: </b> {{loan.amount_approved | moneyString}} Bs.</p>
                                  </v-col>
                                  <v-col cols="12" md="4" v-show="qualification_edit" class="pb-0" >
                                    <v-text-field
                                      dense
                                      label="MONTO SOLICITADO"
                                      v-model="loan.amount_approved"
                                      v-on:keyup.enter="simulator()"
                                      :outlined="true"
                                    ></v-text-field>
                                  </v-col>
                                  <v-col cols="12" md="4" class="pb-0">
                                    <p><b>PROMEDIO LIQUIDO PAGABLE: </b> {{loan.borrower[0].payable_liquid_calculated | moneyString }} Bs.</p>
                                  </v-col>
                                  <v-col cols="12" md="4" class="pb-0" >
                                    <p><b>LIQUIDO PARA CALIFICACION: </b> {{loan.liquid_qualification_calculated | moneyString}} Bs.</p>
                                  </v-col>
                                  <v-col cols="12" md="4" v-show="!qualification_edit" class="py-0">
                                    <p><b>PLAZO:</b>{{' '+loan.loan_term}}</p>
                                  </v-col>
                                  <v-col cols="12" md="4" v-show="qualification_edit" class="py-0" >
                                    <v-text-field
                                      dense
                                      label="PLAZO"
                                      v-model="loan.loan_term"
                                      v-on:keyup.enter="simulator()"
                                      :outlined="true"
                                    ></v-text-field>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0">
                                    <p><b>TOTAL BONOS:</b> {{loan.borrower[0].bonus_calculated | moneyString}}</p>
                                  </v-col>
                                   <v-col cols="12" md="4" class="py-0">
                                    <p><b>LÍMITE DE ENDEUDAMIENTO:</b> {{loan.indebtedness_calculated|percentage }}% </p>
                                  </v-col>
                                  <v-col cols="12" md="4" v-show="qualification_edit" class="py-0">
                                    <center>
                                      <v-btn
                                        class="py-0 text-right"
                                        color="info"
                                        rounded
                                        x-small
                                        @click="simulator()">Calcular
                                      </v-btn>
                                    </center>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0">
                                    <p><b>CALCULO DE CUOTA: </b> {{loan.estimated_quota | moneyString}} Bs.</p>
                                  </v-col>
                                  <v-progress-linear></v-progress-linear>
                                  <BallotsAdjust 
                                    v-if="loan.parent_reason != 'REPROGRAMACIÓN'" 
                                    :ballots="loan.borrower[0].ballots"
                                  />
                                  <v-progress-linear v-show="loan_refinancing.refinancing"></v-progress-linear>
                                    <v-col cols="12" md="6" class="pb-0" v-show="loan_refinancing.refinancing">
                                    <p style="color:teal"><b>DATOS DEL PRÉSTAMO A REFINANCIAR{{' => '+ loan_refinancing.description}}</b></p>
                                  </v-col>
                                  <v-col cols="12" md="6" class="py-0" v-show="loan_refinancing.refinancing">
                                    <EditCancelButton
                                      :editing="collection_edit"
                                      :permission="permissionSimpleSelected.includes('update-refinancing-balance') && $route.query.workTray != 'tracingLoans'"
                                      :onCancel="resetForm"
                                      :onEdit="editRefinancing"
                                      iconEditing="mdi-check"
                                      iconCancel="mdi-close"
                                      iconDefault="mdi-calculator"
                                      textEditing="Actualizar el Saldo"
                                      textCancel="Cancelar"
                                      textDefault="Editar Saldo Refinanciamiento"
                                      :cancelStyle="{ }"
                                      :editStyle="{ }"
                                    />
                                  </v-col>
                                 
                                  <v-row v-show="loan_refinancing.refinancing">
                                  <v-col cols="12" md="3" class="py-2">
                                    <p><b>Codigo Ptmo Padre:</b>{{' '+loan_refinancing.code}}</p>
                                  </v-col>
                                  <v-col cols="12" md="3" class="py-2" >
                                    <p><b>Monto Ptmo Padre:</b> {{loan_refinancing.amount_approved_son | money}}</p>
                                  </v-col>
                                  <v-col cols="12" md="3" class="py-2">
                                    <p><b>Plazo Ptmo Padre:</b>{{' '+loan_refinancing.loan_term}}</p>
                                  </v-col>
                                   <v-col cols="12" md="3" class="py-2">
                                    <p><b>Cuota Ptmo Padre:</b> {{loan_refinancing.estimated_quota | money}}</p>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0">
                                    <p><b>Fecha Desembolso Ptmo Padre:</b> {{loan_refinancing.disbursement_date | date}}</p>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0"  v-show="!collection_edit_sismu" v-if="loan_refinancing.type_sismu==true">
                                    <p><b>Fecha de Corte :</b>
                                    <span v-if="loan_refinancing.date_cut_refinancing">
                                      {{loan_refinancing.date_cut_refinancing | date}}</span>
                                    <span v-else>Sin registar</span></p>
                                  </v-col>
                                  <v-col cols="12" md="4"  v-show="collection_edit_sismu "  class="py-0">
                                    <v-text-field
                                      dense
                                      v-model="loan_refinancing.date_cut_refinancing"
                                      label="Fecha de Corte"
                                      hint="Día/Mes/Año"
                                      type="date"
                                      :outlined="true"
                                   ></v-text-field>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0" v-show="!collection_edit_sismu">
                                    <p><b>Saldo de Préstamo a Refinanciar:</b> {{loan_refinancing.balance_parent_loan_refinancing | money}}</p>
                                  </v-col>
                                  <v-col cols="12" md="4" v-show="collection_edit_sismu " class="py-0" >
                                    <v-text-field
                                      dense
                                      label="Saldo de Prestamo a Refinanciar"
                                      v-model="loan_refinancing.balance_parent_loan_refinancing"
                                     :outlined="true"
                                    ></v-text-field>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0" >
                                    <p class="success--text"><b>Monto Solicitado del Prestamo Nuevo:</b> {{loan_refinancing.amount_approved | money}}</p>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0" >
                                    <p class="success--text"><b>Saldo Anterior de la Deuda:</b> {{loan_refinancing.balance_parent_loan_refinancing | money}}</p>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0" >
                                    <p class="success--text"><b>Monto del Refinanciamiento:</b> {{loan_refinancing.refinancing_balance | money}}</p>
                                  </v-col>
                                  </v-row>
                                  <template v-if="loan.parent_reason == 'REPROGRAMACIÓN'">                                  
                                    <v-col cols="12" md="6" class="pb-0">
                                    <p style="color:teal"><b>REPROGRAMACIÓN - DATOS DEL PRÉSTAMO PADRE</b></p>
                                  </v-col>
                                  <v-row>
                                    <v-col cols="12" md="3" class="py-2">
                                    <p><b>Codigo Ptmo Padre:</b>{{' '+loan.parent_loan.code}}</p>
                                  </v-col>
                                  <v-col cols="12" md="3" class="py-2" >
                                    <p><b>Monto Ptmo Padre:</b> {{loan.parent_loan.amount_approved | money}}</p>
                                  </v-col>
                                  <v-col cols="12" md="3" class="py-2">
                                    <p><b>Plazo Ptmo Padre:</b>{{' '+loan.parent_loan.loan_term}}</p>
                                  </v-col>
                                   <v-col cols="12" md="3" class="py-2">
                                    <p><b>Cuota Ptmo Padre:</b> {{loan.parent_loan.estimated_quota | money}}</p>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0">
                                    <p><b>Fecha Desembolso Ptmo Padre:</b> {{loan.parent_loan.disbursement_date | date}}</p>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0">
                                    <p><b>Saldo Ptmo Padre:</b> {{loan.parent_loan.balance | money}}</p>
                                  </v-col>
                                  <v-col cols="12" md="4" class="py-0">
                                    <p class="success--text"><b>Monto reprogramado:</b> {{loan.parent_loan.balance_for_reprogramming | money}}</p>
                                  </v-col>
                                  </v-row>
                                </template>

                                  <v-progress-linear></v-progress-linear>
                                  <v-col cols="12" md="12" class="pb-0" >
                                    <p style="color:teal"><b>DATOS DEL CONTRATO</b></p>
                                  </v-col>
                                  <v-progress-linear color="blue-grey lighten-3"></v-progress-linear>
                                    <v-col cols="12" md="3">
                                      <v-text-field
                                        dense
                                        v-model="loan.delivery_contract_date"
                                        label="FECHA ENTREGA DE CONTRATO"
                                        hint="Día/Mes/Año"
                                        type="date"
                                        :clearable="edit_delivery_date"
                                        :outlined="edit_delivery_date"
                                        :readonly="!edit_delivery_date"
                                      ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="3" v-if="permissionSimpleSelected.includes('registration-delivery-return-contracts')">
                                      <EditCancelButton
                                        :editing="edit_delivery_date"
                                        :permission="''"
                                        :onCancel="resetForm"
                                        :onEdit="editDateDelivery"
                                        iconEditing="mdi-check"
                                        iconCancel="mdi-close"
                                        iconDefault="mdi-pencil"
                                        textEditing="Guardar Fecha Entrega"
                                        textDefault="Editar Fecha Entrega"
                                        :cancelStyle="{ marginRight: '45px'}"
                                        :editStyle="{ marginRight: '10px'}"
                                      />
                                    </v-col>
                                     <v-col cols="12" md="3"  v-if="!permissionSimpleSelected.includes('registration-delivery-return-contracts')">
                                    </v-col>
                                    <v-col cols="12" md="3">
                                      <v-text-field
                                        dense
                                        v-model="loan.return_contract_date"
                                        label="FECHA RECEPCION DE CONTRATO"
                                        hint="Día/Mes/Año"
                                        type="date"
                                        :clearable="edit_return_date"
                                        :outlined="edit_return_date"
                                        :readonly="!edit_return_date"
                                      ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="3" v-show="removeAccents(loan.delivery_contract_date) != 'Fecha invalida'" v-if="permissionSimpleSelected.includes('registration-delivery-return-contracts')">
                                      <EditCancelButton
                                        :editing="edit_return_date"
                                        :permission="''"
                                        :onCancel="resetForm"
                                        :onEdit="editDateReturn"
                                        iconEditing="mdi-check"
                                        iconCancel="mdi-close"
                                        iconDefault="mdi-pencil"
                                        textEditing="Guardar Fecha Recepción"
                                        textDefault="Editar Fecha Recepción"
                                        :cancelStyle="{ marginRight: '45px'}"
                                        :editStyle="{ marginRight: '10px'}"
                                      />
                                  </v-col>
                                  <v-col cols="12" md="3" v-if="!permissionSimpleSelected.includes('registration-delivery-return-contracts')">
                                  </v-col>
                                  <v-col cols="12" md="3" v-show="removeAccents(loan.delivery_contract_date) == 'Fecha invalida' && permissionSimpleSelected.includes('registration-delivery-return-contracts')" >
                                  </v-col>
                                   <v-col cols="12" md="3">
                                      <v-text-field
                                        dense
                                        v-model="loan.regional_delivery_contract_date"
                                        label="FECHA ENTREGA DE CONTRATO REGIONAL"
                                        hint="Día/Mes/Año"
                                        type="date"
                                        :clearable="edit_delivery_date_regional"
                                        :outlined="edit_delivery_date_regional"
                                        :readonly="!edit_delivery_date_regional"
                                      ></v-text-field>
                                    </v-col>
                                      <v-col cols="12" md="3" v-if="permissionSimpleSelected.includes('registration-delivery-return-contracts')">
                                        <EditCancelButton
                                          :editing="edit_delivery_date_regional"
                                          :permission="''"
                                          :onCancel="resetForm"
                                          :onEdit="editDateDeliveryRegional"
                                          iconEditing="mdi-check"
                                          iconCancel="mdi-close"
                                          iconDefault="mdi-pencil"
                                          textEditing="Guardar Fecha Entrega Regional"
                                          textDefault="Editar Fecha Entrega Regional"
                                          :cancelStyle="{ marginRight: '45px'}"
                                          :editStyle="{ marginRight: '10px'}"
                                        />
                                    </v-col>
                                      <v-col cols="12" md="3" v-if="!permissionSimpleSelected.includes('registration-delivery-return-contracts')">
                                    </v-col>
                                    <v-col cols="12" md="3">
                                      <v-text-field
                                        dense
                                        v-model="loan.regional_return_contract_date"
                                        label="FECHA RECEPCION DE CONTRATO REGIONAL"
                                        hint="Día/Mes/Año"
                                        type="date"
                                        :clearable="edit_return_date_regional"
                                        :outlined="edit_return_date_regional"
                                        :readonly="!edit_return_date_regional"
                                      ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="3" v-show="removeAccents(loan.regional_delivery_contract_date) != 'Fecha invalida'" v-if="permissionSimpleSelected.includes('registration-delivery-return-contracts') && $route.query.workTray != 'tracingLoans'">
                                      <EditCancelButton
                                          :editing="edit_return_date_regional"
                                          :permission="''"
                                          :onCancel="resetForm"
                                          :onEdit="editDateReturnRegional"
                                          iconEditing="mdi-check"
                                          iconCancel="mdi-close"
                                          iconDefault="mdi-pencil"
                                          textEditing="Guardar Fecha Recepcion"
                                          textDefault="Guardar Fecha Recepcion"
                                          :cancelStyle="{ marginRight: '45px'}"
                                          :editStyle="{ marginRight: '10px'}"
                                        />
                                    </v-col>
                                    <v-col cols="12" md="3" v-if="!permissionSimpleSelected.includes('registration-delivery-return-contracts')">
                                  </v-col>
                                  <v-col cols="12" md="3" v-show="removeAccents(loan.regional_delivery_contract_date) == 'Fecha invalida' && permissionSimpleSelected.includes('registration-delivery-return-contracts')" >
                                  </v-col>
                                  <v-col cols="12" md="3">
                                      <v-text-field
                                        dense
                                        v-model="loan.contract_signature_date"
                                        label="FECHA FIRMA CONTRATO"
                                        hint="Día/Mes/Año"
                                        type="date"
                                        :clearable="edit_date_contract"
                                        :outlined="edit_date_contract"
                                        :readonly="!edit_date_contract"
                                      ></v-text-field>
                                    </v-col>
                                      <v-col cols="12" md="3" v-if="permissionSimpleSelected.includes('registration-date-contract')">
                                        <EditCancelButton
                                          :editing="edit_date_contract"
                                          :permission="''"
                                          :onCancel="resetForm"
                                          :onEdit="editDateContract"
                                          iconEditing="mdi-check"
                                          iconCancel="mdi-close"
                                          iconDefault="mdi-pencil"
                                          textEditing="Editar Fecha Contrato"
                                          textDefault="Guardar Fecha Recepcion"
                                          :cancelStyle="{ marginRight: '45px'}"
                                          :editStyle="{ marginRight: '10px'}"
                                        />
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
                                  <div >
                                    <v-col cols="12" md="12">
                                    <ul style="list-style: none" class="pa-0" v-if="loan.modality.procedure_type.name== 'Préstamo a Largo Plazo' || loan.modality.procedure_type.name == 'Préstamo a Corto Plazo'|| loan.modality.procedure_type.name == 'Refinanciamiento Préstamo a Corto Plazo' || loan.modality.procedure_type.name == 'Refinanciamiento Préstamo a Largo Plazo' || loan.modality.procedure_type.name == 'Préstamo Anticipo'">
                                      <li v-for="(guarantor) in loan.borrowerguarantors" :key="guarantor.id">
                                        <v-col cols="12" md="12" class="pa-0 mb-0">
                                          <v-row class="pa-2">
                                            <v-col cols="12" md="12" class="py-0">
                                              <p style="color:teal"><b>GARANTE
                                                <v-tooltip top v-if="permissionSimpleSelected.includes('show-affiliate')">
                                                  <template v-slot:activator="{ on }">
                                                    <v-btn
                                                      icon
                                                      dark
                                                      small
                                                      color="warning"
                                                      bottom
                                                      right
                                                      v-on="on"
                                                      :to="{name: 'affiliateAdd', params: { id: guarantor.affiliate_id}}"
                                                      target="_blank"
                                                    >
                                                      <v-icon>mdi-eye</v-icon>
                                                    </v-btn>
                                                  </template>
                                                  <span>Ver datos del afiliado</span>
                                                </v-tooltip>
                                                <v-tooltip top>
                                                  <template v-slot:activator="{ on }">
                                                    <v-btn
                                                      icon
                                                      dark
                                                      x-small
                                                      :color="'error'"
                                                      top
                                                      right
                                                      v-on="on"
                                                      @click.stop="resetForm()"
                                                      v-show="edit_update_loan_affiliates"
                                                    >
                                                    <v-icon>mdi-close</v-icon>
                                                    </v-btn>
                                                  </template>
                                                  <div>
                                                    <span>Cancelar</span>
                                                  </div>
                                                </v-tooltip>
                                                <v-tooltip top v-if="permissionSimpleSelected.includes('update-loan-calculations')">
                                                  <template v-slot:activator="{ on }">
                                                    <v-btn
                                                      icon
                                                      dark
                                                      x-small
                                                      :color="edit_update_loan_affiliates ? 'danger' : 'success'"
                                                      top
                                                      right
                                                      v-on="on"
                                                      @click.stop="update_loan_affiliates(guarantor.id, guarantor.indebtedness_calculated, guarantor.payable_liquid_calculated, guarantor.liquid_qualification_calculated)"
                                                    >
                                                      <v-icon v-if="edit_update_loan_affiliates">mdi-check</v-icon>
                                                      <v-icon v-else>mdi-pencil</v-icon>
                                                    </v-btn>
                                                  </template>
                                                  <div>
                                                    <span v-if="edit_update_loan_affiliates">Guardar Límite Garante</span>
                                                    <span v-else>Editar </span>
                                                  </div>
                                                </v-tooltip>
                                              </b></p>
                                            </v-col>
                                            <v-progress-linear></v-progress-linear><br>
                                            <v-col class="my-0 py-0" cols="12" md="3">
                                              <p><b>NOMBRE:</b> {{$options.filters.fullName(guarantor, true)}}</p>
                                            </v-col>
                                            <v-col class="my-0 py-0" cols="12" md="3">
                                              <p><b>CÉDULA DE IDENTIDAD:</b> {{guarantor.identity_card}}</p>
                                            </v-col>
                                            <v-col class="my-0 py-0" cols="12" md="3">
                                              <p><b>TELÉFONO:</b> {{guarantor.cell_phone_number}}</p>
                                            </v-col>
                                            <v-col class="my-0 py-0" cols="12" md="3">
                                              <p><b>PORCENTAJE DE PAGO:</b> {{guarantor.payment_percentage | percentage }}%</p>
                                            </v-col>
                                             <v-col class="my-0 py-0" cols="12" md="3" v-show="!edit_update_loan_affiliates">
                                              <p><b>LIQUIDO PARA CALIFICACION:</b> {{guarantor.payable_liquid_calculated | moneyString}}</p>
                                            </v-col>
                                            <v-col cols="12" md="3" v-show="edit_update_loan_affiliates" class="pb-0" >
                                              <v-text-field
                                                dense
                                                label="LIQUIDO PAGABLE"
                                                v-model="guarantor.payable_liquid_calculated"
                                                :outlined="true"
                                              ></v-text-field>
                                            </v-col>
                                            <v-col class="my-0 py-0" cols="12" md="3">
                                              <p><b>PROMEDIO DE BONOS:</b> {{guarantor.bonus_calculated | moneyString }}</p>
                                            </v-col>
                                            <v-col class="my-0 py-0" cols="12" md="3" v-show="!edit_update_loan_affiliates">
                                              <p><b>LIQUIDO PARA CALIFICACION:</b> {{guarantor.liquid_qualification_calculated | moneyString}}</p>
                                            </v-col>
                                            <v-col cols="12" md="3" v-show="edit_update_loan_affiliates" class="pb-0" >
                                              <v-text-field
                                                dense
                                                label="LIQUIDO PARA CALIFICACION"
                                                v-model="guarantor.liquid_qualification_calculated"
                                                :outlined="true"
                                              ></v-text-field>
                                            </v-col>
                                            <v-col class="my-0 py-0" cols="12" md="3" v-show="!edit_update_loan_affiliates">
                                              <p><b>LÍMITE DE ENDEUDAMIENTO CALCULADO:</b> {{guarantor.indebtedness_calculated | percentage }}%</p>
                                            </v-col>
                                            <v-col class="my-0 py-0" cols="12" md="3" v-show="!edit_update_loan_affiliates">
                                              <p><b>MONTO DE EVALUACIÓN A GARANTE:</b> {{guarantor.eval_quota | moneyString }}</p>
                                            </v-col>
                                            <v-col cols="12" md="3" v-show="edit_update_loan_affiliates" class="pb-0" >
                                              <v-text-field
                                                dense
                                                label="LÍMITE DE ENDEUDAMIENTO CALCULADO"
                                                v-model="guarantor.indebtedness_calculated"
                                                :outlined="true"
                                              ></v-text-field>
                                            </v-col>
                                          </v-row>
                                        </v-col>
                                        <BallotsAdjust :ballots="guarantor.ballots"/>
                                        <GuaranteesTable :borrowerguarantors="guarantor.active_guarantees"/>
                                      </li>
                                      <br>
                                      <p v-if="loan.guarantors.length==0" style="color:teal"><b> NO TIENE GARANTES </b></p>
                                    </ul>
                                    </v-col>
                                    <v-col cols="12" md="12" v-if="loan.modality.procedure_type.second_name == 'Fondo de Retiro'">
                                      <v-row>
                                        <p style="color:teal"><b>GARANTIA DEL FONDO DE RETIRO POLICIAL SOLIDARIO</b> </p>
                                        <v-progress-linear></v-progress-linear><br>
                                      <v-col class="my-0 py-0" cols="12" md="12">
                                        <p><b>TOTAL BENEFICIO DEL FONDO DE RETIRO POLICIAL SOLIDARIO: </b>{{loan.retirement.average | moneyString}} </p>
                                      </v-col>
                                      <v-col class="my-0 py-0" cols="12" md="12">
                                        <p><b>COBERTURA DEL BENEFICIO DEL FONDO DE RETIRO POLICIAL SOLIDARIOS: </b>{{loan.retirement.coverage | moneyString}} </p>
                                      </v-col>
                                      <v-col class="my-0 py-0" cols="12" md="12">
                                        <p><b>PORCENTAJE CALCULADO: </b>{{loan.retirement.percentage * 100}} % </p>
                                      </v-col>
                                      </v-row>
                                    </v-col>
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
                                  <v-dialog v-model="dialog_edit" max-width="500px" >
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
                                              <v-col cols="12" sm="6" md="3" >
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem.second_name"
                                                  label="Segundo Nombre"
                                                ></v-text-field>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="3" >
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem.last_name"
                                                  label="Primer Apellido"
                                                ></v-text-field>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="3">
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem.mothers_last_name"
                                                  label="Segundo Apellido"
                                                ></v-text-field>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="3" >
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem.phone_number"
                                                  label="Teléfono"
                                                  v-mask="'(#) ###-###'"
                                                ></v-text-field>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="3" >
                                                <v-text-field
                                                  dense
                                                  v-model="editedItem.cell_phone_number"
                                                  label="Celular"
                                                  v-mask="'(###)-#####'"
                                                ></v-text-field>
                                              </v-col>
                                              <v-col cols="12" sm="6" md="3">
                                                <v-select
                                                  :error-messages="errors"
                                                  dense
                                                  :items="kinships"
                                                  item-text="name"
                                                  item-value="id"
                                                  v-model="editedItem.kinship_id"
                                                  label="Parentesco"
                                                >
                                                </v-select>
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
                                            @click="savePersonReference()"
                                          >
                                            Guardar
                                          </v-btn>
                                        </v-card-actions>
                                      </v-card>
                                    </v-dialog>
                                </template>
                                <template v-slot:[`item.kinship_id`]="{ item }">
                                  {{item.kinship_id != null? kinships.find(o => o.id == item.kinship_id).name:'' }}
                                </template>
                                <template v-slot:[`item.actions`]="{ item }" v-if="permissionSimpleSelected.includes('update-reference-cosigner') && $route.query.workTray != 'tracingLoans'">
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
                                              style="margin-right: 45px;margin-top:15px"
                                              @click.stop="resetForm()"
                                              v-show="edit_disbursement"
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
                                              :color="edit_disbursement ? 'danger' : 'success'"
                                              top
                                              right
                                              absolute
                                              v-on="on"
                                              style="margin-right: -9px; margin-top:15px"
                                              @click.stop="editLoan()"
                                            >
                                              <v-icon v-if="edit_disbursement">mdi-check</v-icon>
                                              <v-icon v-else>mdi-pencil</v-icon>
                                            </v-btn>
                                          </template>
                                          <div>
                                            <span v-if="edit_disbursement">Guardar</span>
                                            <span v-else>Editar</span>
                                          </div>
                                        </v-tooltip>
                                      </div>
                                    <v-row>
                                      <v-tooltip top>
                                        <template v-slot:activator="{ on }">
                                          <v-btn
                                            fab
                                            dark
                                            x-small
                                            :color="'error'"
                                            absolute
                                            top
                                            right
                                            v-on="on"
                                            @click.stop="resetForm()"
                                            v-show="edit_number_payment_type"
                                            style="margin-right: 60px; margin-top: 30px"
                                          >
                                          <v-icon>mdi-close</v-icon>
                                          </v-btn>
                                        </template>
                                        <div>
                                          <span>Cancelar</span>
                                        </div>
                                      </v-tooltip>
                                      <v-tooltip top v-if="permissionSimpleSelected.includes('update-reference-cosigner')">
                                        <template v-slot:activator="{ on }">
                                          <v-btn
                                            v-if="loan.payment_type && loan.payment_type.name =='Depósito Bancario'"
                                            fab
                                            dark
                                            x-small
                                            :color="edit_number_payment_type ? 'danger' : 'success'"
                                            absolute
                                            top
                                            right
                                            v-on="on"
                                            @click.stop="update_number_payment_type()"
                                            style="margin-right: 25px; margin-top: 30px"
                                          >
                                            <v-icon v-if="edit_number_payment_type">mdi-check</v-icon>
                                            <v-icon v-else>mdi-pencil</v-icon>
                                          </v-btn>
                                        </template>
                                        <div>
                                          <span v-if="edit_number_payment_type">Guardar información bancaria</span>
                                          <span v-else>Editar datos financieros </span>
                                        </div>
                                      </v-tooltip>
                                      <v-progress-linear></v-progress-linear><br>
                                      <v-col cols="12" md="4">
                                        <p><b>TIPO DE DESEMBOLSO:</b> {{loan.payment_type ? loan.payment_type.name : 'Sin registro'}}</p>
                                      </v-col>
                                      <v-col cols="12" md="3" v-if="loan.payment_type && loan.payment_type.name =='Depósito Bancario' && edit_number_payment_type == false">
                                        <p><b>ENTIDAD FINANCIERA:</b>{{' '+financial_account}}</p>
                                      </v-col>
                                      <v-col cols="12" md="3" v-if="loan.payment_type && loan.payment_type.name =='Depósito Bancario' && edit_number_payment_type == true">
                                        <v-select
                                          dense
                                          :loading="loading"
                                          :items="entity"
                                          item-text="name"
                                          item-value="id"
                                          label="Entidad Financiera"
                                          v-model="loan.financial_entity_id"
                                        ></v-select>
                                      </v-col>
                                      <v-col cols="12" md="3" v-if="loan.payment_type && loan.payment_type.name =='Depósito Bancario' && edit_number_payment_type == false">
                                        <p><b>NUMERO DE CUENTA:</b>{{' '+loan.number_payment_type}}</p>
                                      </v-col>
                                      <v-col cols="12" md="3" v-show="loan.payment_type && loan.payment_type.name =='Depósito Bancario'&& edit_number_payment_type == true">
                                        <v-text-field
                                          dense
                                          label="NUMERO DE CUENTA"
                                          v-model="loan.number_payment_type"
                                          :outlined="true"
                                        ></v-text-field>
                                      </v-col>
                                      <v-col cols="12" md="3" v-show="loan.payment_type && loan.payment_type.name =='Depósito Bancario'">
                                        <p><b>CUENTA SIGEP:</b> {{' '+loan.borrower[0].sigep_status}}</p>
                                      </v-col>
                                       <v-col cols="12" md="4">
                                        <div class="py-0">
                                          <v-text-field
                                            dense
                                            :outlined="permissionSimpleSelected.includes('update-accounting-voucher') ? edit_disbursement : false"
                                            :readonly="permissionSimpleSelected.includes('update-accounting-voucher') ? !edit_disbursement : true"
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
                                         :outlined="permissionSimpleSelected.includes('change-disbursement-date') ? edit_disbursement : false"
                                          :readonly="permissionSimpleSelected.includes('change-disbursement-date') ? !edit_disbursement : true"
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
          <v-dialog
            v-model="dialog"
            max-width="500"
          >
            <v-card>
              <v-card-title>
                Esta seguro de generar el corte del prestamo?
              </v-card-title>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                  color="red darken-1"
                  text
                  @click="closeRefinancingCut()"
                >
                  Cancelar
                </v-btn>
                <v-btn
                  color="green darken-1"
                  text
                  @click="saveRefinancingCut()"
                >
                  Aceptar
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>

        <!--/v-card-->
      </v-form>
    </ValidationObserver>
  </v-container>
</template>
<script>
import BallotsAdjust from "@/components/workflow/BallotsAdjust"
import GuaranteesTable from "@/components/workflow/GuaranteesTable"
import common from "@/plugins/common"
import EditCancelButton from '@/components/shared/EditCancelButton.vue'

export default {
  name: "specific-data-loan",
  components:{
    BallotsAdjust,
    GuaranteesTable,
    EditCancelButton
  },
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
      dialog: false, //dialog de confirmacion de corte del prestamo
      dialog_edit: false, //dialog para editar los datos de la persona de referencia
      dialog_codeptor: false, //dialog para editar codeudor no afiliado

      //Variables que sirven para habilitar los imputs y editarlos

      qualification_edit:false,
      collection_edit:false,
      collection_edit_sismu:false,
      edit_return_date : false,
      edit_delivery_date : false,
      edit_return_date_regional : false,
      edit_delivery_date_regional : false,
      edit_date_contract: false,

      edit_disbursement: false,
      edit_update_loan_affiliates: false,
      reload: false,
      payment_types:[],
      city: [],
      entity: [],
      entities:null,
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
        { text: 'TELÉFONO', value: 'phone_number' },
        { text: 'CELULAR', value: 'cell_phone_number' },
        { text: 'DIRECCION ', value: 'address' },
        { text: "Partentesco", value: "kinship_id"
      },
        {
      text: "Actions",
      value: "actions",
      sortable: false
    }
      ],
    edit_number_payment_type: false,
    kinships: []
  }),
  beforeMount(){
    this.getPaymentTypes()
    this.getCity()
    this.getEntity()
    this.getKinship()
  },
  computed: {
      //Metodo para obtener Permisos por rol
      permissionSimpleSelected () {
        return this.$store.getters.permissionSimpleSelected
      },
      //FIXME Eliminar si no se usa. Metodo para obtener la entidad financiera
      financial_account() {
       for (this.i = 0; this.i< this.entity.length; this.i++) {
        if(this.loan.financial_entity_id==this.entity[this.i].id)
        {
          this.entities= this.entity[this.i].name
        }
      }
      return this.entities
    }
  },
  watch: {
      dialog_edit (val) {
        val || this.close()
      },
    },

created(){
  this.removeAccents = common.removeAccents
},

  methods:{
    //Metodo para guardar persona de Referencia
    async savePersonReference(){
      let res = await axios.patch(`personal_reference/${this.editedItem.id}`, {
        first_name:this.editedItem.first_name,
        second_name:this.editedItem.second_name,
        last_name:this.editedItem.last_name,
        mothers_last_name:this.editedItem.mothers_last_name,
        phone_number:this.editedItem.phone_number,
        cell_phone_number:this.editedItem.cell_phone_number,
        address:this.editedItem.address,
        kinship_id: this.editedItem.kinship_id
        })
        this.toastr.success('Se registró correctamente.')
    this.close()
    this.$forceUpdate()
    },
    //Metodo para guardar persona de referencia
    async saveCodeptor(){
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
    //Metodo para obtener los datos para el guardado del codeudor
      editItem1 (item) {
        this.editedItem1 =  item
        this.dialog_codeptor = true
      },
    //Metodo para obtener los datos para el guardado de persona de referencia
      editItem (item) {
        this.editedItem =  item
        this.dialog_edit = true
      },
    //Metodo para cerrar el modal del guardado del codeudor
      closeCodeptor() {
        this.dialog_codeptor = false
        this.$nextTick(() => {
          this.editedItem1 = Object.assign({}, this.defaultItem1)
          this.editedIndex = -1
        })
      },
    //Metodo para cerrar el modal del guardado de persona de referencia
      close () {
        this.dialog_edit = false
        this.$nextTick(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        })
      },
    //Metodo para obtener las entidades financieras
    async getEntity() {
      try {
        this.loading = true
        let res = await axios.get(`financial_entity`)
        this.entity = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo para obtener las ciudades
    async getCity() {
      try {
        this.loading = true
        let res = await axios.get(`city`)
        this.city = res.data
     } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo para obtener la extencion del ci
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
    //Metodo para limpiar los campos
    resetForm() {
      this.edit_disbursement = false
      this.qualification_edit = false
      this.edit_update_loan_affiliates = false
      this.collection_edit=false
      this.collection_edit_sismu=false
      this.edit_return_date = false
      this.edit_delivery_date = false
      this.edit_return_date_regional = false
      this.edit_delivery_date_regional = false
      this.edit_date_contract = false
      this.reload = true
      if(this.loan_refinancing.type_sismu==true){
        this.loan_refinancing.balance= this.loan.balance_parent_loan_refinancing
      }else{
        this.loan_refinancing.balance= this.loan.balance_parent_loan_refinancing
      }
      //Obtener y volver a los datos antiguos de la variable
      this.loan_refinancing.date_cut_refinancing= this.loan.date_cut_refinancing
      this.loan.amount_approved = this.loan.amount_approved_aux
      this.loan.lenders[0].payable_liquid_calculated = this.loan.payable_liquid_calculated_aux
      this.loan.liquid_qualification_calculated = this.loan.liquid_qualification_calculated_aux
      this.loan.loan_term = this.loan.loan_term_aux
      this.loan.lenders[0].bonus_calculated = this.loan.bonus_calculated_aux
      this.loan.indebtedness_calculated = this.loan.indebtedness_calculated_aux
      this.loan.estimated_quota = this.loan.estimated_quota_aux
      this.edit_number_payment_type = false
      this.$nextTick(() => {
      this.reload = false
      })
    },
    //Metodo para el calculo del monto al editar
    async simulator() {
    try {
      let res = await axios.post(`simulator`, {
        procedure_modality_id:this.loan.procedure_modality_id,
        amount_requested: this.loan.amount_approved,
        months_term:  this.loan.loan_term,
        guarantor: false,
        liquid_qualification_calculated_lender: 0,
        liquid_calculated:[
          {
            affiliate_id: this.loan.borrower[0].id,
            liquid_qualification_calculated: this.loan.borrower[0].liquid_qualification_calculated
          }
        ]
    })
      if(res.data.amount_requested  > res.data.amount_maximum_suggested )
      {
          this.loan.amount_approved = res.data.amount_maximum_suggested
      }
      else{
          this.loan.amount_approved = res.data.amount_requested
      }
          this.loan.loan_term = res.data.months_term
          this.loan.indebtedness_calculated = res.data.indebtedness_calculated_total
          this.loan.estimated_quota = res.data.quota_calculated_estimated_total
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo para editar fecha de entrega de contrato
    async editDateDelivery(){
    try {
      if (!this.edit_delivery_date) {
        this.edit_delivery_date = true
      } else {
          let res = await axios.patch(`loan/${this.loan.id}`, {
          delivery_contract_date:this.loan.delivery_contract_date,
          current_role_id: this.$store.getters.rolePermissionSelected.id
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
    //Metodo para editar fecha de retorno de contrato
    async editDateReturn(){
      try {
        if (!this.edit_return_date) {
          this.edit_return_date = true
        } else {
            let res = await axios.patch(`loan/${this.loan.id}`, {
            return_contract_date: this.loan.return_contract_date,
            current_role_id: this.$store.getters.rolePermissionSelected.id
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
    //Metodo para editar fecha de entrega de contrato regional
    async editDateDeliveryRegional(){
      try {
        if (!this.edit_delivery_date_regional) {
          this.edit_delivery_date_regional = true
        } else {
            let res = await axios.patch(`loan/${this.loan.id}`, {
            regional_delivery_contract_date:this.loan.regional_delivery_contract_date,
            current_role_id: this.$store.getters.rolePermissionSelected.id
           })
            this.toastr.success('Se registró correctamente.')
            this.edit_delivery_date_regional = false
         }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo para editar fecha de retorno de contrato regional
    async editDateReturnRegional(){
      try {
        if (!this.edit_return_date_regional) {
          this.edit_return_date_regional = true
        } else {
            let res = await axios.patch(`loan/${this.loan.id}`, {
              regional_return_contract_date: this.loan.regional_return_contract_date,
              current_role_id: this.$store.getters.rolePermissionSelected.id
          })
            this.toastr.success('Se registró correctamente.')
            this.edit_return_date_regional = false
         }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async editDateContract(){
      try {
        if (!this.edit_date_contract) {
          this.edit_date_contract = true
        } else {
            let res = await axios.patch(`loan/${this.loan.id}`, {
              contract_signature_date: this.loan.contract_signature_date,
              current_role_id: this.$store.getters.rolePermissionSelected.id
          })
            this.toastr.success('Se registró correctamente.')
            this.edit_date_contract = false
         }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo para ingresar la fecha de desembolso
    async editLoan(){
      try {
        if (!this.edit_disbursement) {
          this.edit_disbursement = true
         } else {
            if(this.removeAccents(this.loan.disbursement_date) =='Fecha invalida'){
              let res = await axios.patch(`loan/${this.loan.id}`, {
               num_accounting_voucher: this.loan.num_accounting_voucher,
               current_role_id: this.$store.getters.rolePermissionSelected.id
            })
          }else{
          let res = await axios.patch(`loan/${this.loan.id}`, {
            disbursement_date:this.loan.disbursement_date,
            date_signal:false,
            current_role_id: this.$store.getters.rolePermissionSelected.id
          })
        }
            this.toastr.success('Se registró correctamente.')
            this.edit_disbursement = false
         }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Cerrar modal de corte de refinanciamiento
    closeRefinancingCut(){
      this.dialog = false
      this.resetForm()
    },
    //Metodo para guardar el corte de refinanciamiento PVT
    async  saveRefinancingCut(){
    try {
      let res = await axios.patch(`loan/${this.loan.id}/update_refinancing_balance`,{
        current_role_id: this.$store.getters.rolePermissionSelected.id
      })
      this.loan_refinancing.refinancing_balance= res.data.refinancing_balance
      this.loan_refinancing.balance_parent_loan_refinancing= res.data.balance_parent_loan_refinancing
      this.toastr.success('Se Actualizó Correctamente.')
      this.collection_edit = false
      this.dialog=false
    } catch (e) {
        this.toastr.error("Ocurrió un error en la impresión.")
        console.log(e)
      }
    },
    //Metodo para guardar el corte de refinanciamiento SISMU
    async editRefinancing(){
      try {
        if (!this.collection_edit) {
          this.collection_edit = true
          if(this.loan_refinancing.type_sismu){
            this.collection_edit_sismu= true
          }else{
            this.dialog=true
            this.collection_edit_sismu= false
          }
        } else {
            if(this.loan_refinancing.type_sismu==true){
                 let res1 = await axios.patch(`loan/${this.loan.id}/sismu`, {
                 data_loan:[{
                    date_cut_refinancing: this.loan_refinancing.date_cut_refinancing,
                    balance : this.loan_refinancing.balance_parent_loan_refinancing,
                    current_role_id: this.$store.getters.rolePermissionSelected.id
                  }
                 ]
               })
            let res = await axios.patch(`loan/${this.loan.id}/update_refinancing_balance`, {
              current_role_id: this.$store.getters.rolePermissionSelected.id
            })
            this.loan_refinancing.refinancing_balance= res.data.refinancing_balance
            this.loan_refinancing.balance_parent_loan_refinancing= res.data.balance_parent_loan_refinancing
            this.toastr.success('Se Actualizó Correctamente.')
            this.collection_edit = false
            this.collection_edit_sismu= false
          }else{
            this.collection_edit_sismu=false
          }
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo para editar el monto y plazo
    async editSimulator(){
      try {
        if (!this.qualification_edit) {
          this.qualification_edit = true
        } else {
          let res = await axios.patch(`edit_loan/${this.loan.id}/qualification`, {
            amount_approved: this.loan.amount_approved,
            loan_term: this.loan.loan_term,
            current_role_id: this.$store.getters.rolePermissionSelected.id
          })
          if(!res.data.messaje){
             this.toastr.error(res.data.message)
          }
          if(res.data.id){
            this.toastr.success('Se registró correctamente.')
          }
            this.qualification_edit = false
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },

    //Metodo para obtener los tipos de pago
    async getPaymentTypes() {
      try {
        this.loading = true
        let res = await axios.get(`payment_type`)
        this.payment_types = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async update_loan_affiliates(affiliate_id, indebtedness_calculated_input, payable_liquid_calculated, liquid_qualification_calculated){
      try {
        if (!this.edit_update_loan_affiliates) {
          this.edit_update_loan_affiliates = true
        }else{
          let res = await axios.post(`loan/update_loan_affiliates`,{
            loan_id: this.loan.id,
            affiliate_id: affiliate_id,
            indebtedness_calculated_input: indebtedness_calculated_input,
            payable_liquid_calculated_input: payable_liquid_calculated,
            liquid_qualification_calculated_input: liquid_qualification_calculated,
          })
        this.edit_update_loan_affiliates = false
        }
      } catch (e) {
        console.log(e)
      }
    },
    async update_number_payment_type(){
      try {
        if (!this.edit_number_payment_type) {
          this.edit_number_payment_type = true
        }else{
          let res = await axios.post(`update_number_payment_type`,{
            loan_id: this.loan.id,
            number_payment_type: this.loan.number_payment_type,
            financial_entity_id: this.loan.financial_entity_id,
            current_role_id: this.$store.getters.rolePermissionSelected.id
          })
        this.edit_number_payment_type = false
        }
      } catch (e) {
        console.log(e)
      }
    },
    async getKinship(){
      try {
        let res = await axios.get('kinship')
        this.kinships = res.data
        console.log(this.kinships)
      } catch (e) {
        console.log(e)
      }
    }
  }
}
</script>
