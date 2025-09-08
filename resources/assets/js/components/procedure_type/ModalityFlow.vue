<template>
    <v-container fluid>
        <v-card flat>
            <v-card-title class="pa-0">
                <v-toolbar dense color="tertiary">
                    <v-toolbar-title>ASIGNACIÓN DE FLUJO A MODALIDADES</v-toolbar-title>
                </v-toolbar>
            </v-card-title>

            <v-expansion-panels 
                v-model="openPanels" 
                multiple 
                class="pt-4"
            >
                <v-expansion-panel v-for="(procedure_modality, index) in procedure_modalities" :key="procedure_modality.id">
                    <v-expansion-panel-header>
                        <span class="text-uppercase font-weight-bold">{{ procedure_modality.name }}</span>
                    </v-expansion-panel-header>

                    <v-expansion-panel-content>
                        <v-data-table 
                            :headers="headers"
                            :items="procedure_modality.procedure_modalities"
                            dense
                            hide-default-footer
                            class="mx-4"
                        >
                            <template v-slot:item="{ item }">
                                <tr class="ma-0 pa-0 ">
                                    <td>{{ item.name }}</td>
                                    <td>{{ item.shortened }}</td>
                                    <td>
                                        <v-select 
                                            :items="workflows" 
                                            item-text="name" 
                                            item-value="id"
                                            v-model="item.workflow_id" 
                                            outlined 
                                            dense
                                            @change="guardarFlujo(item)"
                                            class="pt-2 "
                                        ></v-select>
                                    </td>
                                </tr>
                            </template>
                        </v-data-table>
                    </v-expansion-panel-content>
                </v-expansion-panel>
            </v-expansion-panels>
        </v-card>
    </v-container>
</template>

<script>
export default {
    data() {
        return {
            procedure_modalities: [], 
            workflows: [], 
            openPanels: [],
            headers: [
                { 
                    text: "Modalidad", 
                    align: "start",            
                    class: ['normal', 'white--text'],
                    align: 'center',
                    value: "name",
                    width: "40%"
                },
                { 
                    text: "Abreviatura",  
                    class: ['normal', 'white--text'],
                    align: 'center',
                    value: "shortened",
                    width: "20%"
                },
                { 
                    text: "Flujo", 
                    class: ['normal', 'white--text'],
                    align: 'center',
                    value: "workflow_id",
                    width: "40%"
                }
            ]
        };
    },
    mounted() {
        this.getModalities();
        this.getWorkflows();
    },
    methods: {
        async getModalities() {
            try {
                let res = await axios.get("get_loan_modalities");
                this.procedure_modalities = res.data;
                
                // Expande todos los paneles por defecto
                this.openPanels = this.procedure_modalities.map((_, index) => index);
            } catch (error) {
                console.error("Error al obtener modalidades", error);
            }
        },
        async getWorkflows() {
            try {
                let res = await axios.get("workflow");
                this.workflows = res.data;
            } catch (error) {
                console.error("Error al obtener workflows", error);
            }
        },
        async guardarFlujo(procedure_modality) {
            try {
                await axios.post(`asign_flow/${procedure_modality.id}`, {
                    workflow_id: procedure_modality.workflow_id
                });
                this.toastr.success("Flujo asignado correctamente");
            } catch (error) {
                console.error("Error al guardar el flujo", error);
                this.toastr.error("No se puede asignar el flujo, ocurrio un error");
            }
        }
    }
};
</script>
