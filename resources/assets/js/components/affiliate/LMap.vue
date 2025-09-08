<template>
  <v-container>
    <v-row>
      <v-col cols="3">
        <v-row style="border: 1px solid #ccc;">
          <v-col cols="12" md="12">
            <div><strong>Buscar la ubicación en:</strong> {{ departamentoSeleccionado }}</div>
          </v-col>
          <v-col cols="12" md="12" class="py-0 my-0">
            <v-autocomplete
              dense
              filled
              outlined
              shaped
              label="Buscar por provincia o municipio"
              v-model="provinciaSeleccionada"
              :items="provinciasFiltradas"
              @change="buscarDireccion"
              @focus="limpiarCamposGoogleMaps"
              clearable
              class="py-0 my-0"
            />
          </v-col>
          <v-col cols="12" md="12" class="py-0 my-0">
            <v-text-field
              shaped
              label="Buscar un término de calle, avenida, plaza u otro lugar"
              v-model="direccion"
              @keyup.enter="buscarDireccion"
              append-icon="mdi-magnify"
              @click:append="buscarDireccion"
              dense
              outlined
              @focus="limpiarCamposGoogleMaps"
              clearable
              class="py-0 my-0"
            />
          </v-col>
          <v-col cols="12" md="12">
            <div><strong>Buscar ubicación por URL de Google Maps</strong></div>
          </v-col>
          <v-col cols="12" md="12" class="py-0 my-0">
            <v-textarea
              shaped
              v-model="url"
              label="Pega la URL compartida de Google Maps"
              @keyup.enter="procesarURL"
              append-icon="mdi-map-marker"
              @click:append="procesarURL"
              clearable
              @focus="limpiarCampos"
              rows="4"
              outlined
              class="py-0 my-0"
            />
          </v-col>
          <v-col cols="12" md="12">
            <div><strong>Buscar ubicación por coordenadas</strong></div>
          </v-col>
          <v-col cols="12" md="5" class="py-0 my-0">
            <v-text-field 
              outlined 
              shaped
              dense 
              label="Latitud" 
              v-model="latInput"
              class="py-0 my-0"
            />
          </v-col>
          <v-col cols="12" md="5" class="py-0 my-0">
            <v-text-field 
              outlined
              shaped 
              dense 
              label="Longitud" 
              v-model="lngInput" 
              class="py-0 my-0"
              @keyup.enter="centrarPorCoordenadas"
            />
          </v-col>
          <v-col cols="12" md="2" class="py-0 my-0">
            <template>
              <v-tooltip top>
                <template v-slot:activator="{ on }">
                  <v-btn 
                    icon 
                    color="primary" 
                    @click="centrarPorCoordenadas"
                    class="py-0 my-0"
                    v-on="on"
                  >
                    <v-icon>mdi-crosshairs-gps</v-icon>
                  </v-btn>
                </template>
                Buscar ubicación por coordenadas
              </v-tooltip>
            </template>
          </v-col>
          <!-- <v-col v-if="direccionSeleccionada" class="py-0 my-0">
            <v-btn color="primary" @click="imprimirComoImagen">
              Imprimir ubicación
            </v-btn>
          </v-col> -->
        </v-row>
      </v-col>

      <v-col cols="9" class="py-0 my-0">
        <div id="map" style="height: 700px;"  class="py-0 my-0"></div>

        <!-- Este se oculta al imprimir -->
        <p class="no-print">
          <strong>Ubicación seleccionada en mapa:</strong> {{ direccionSeleccionada }}
        </p>
      </v-col>

    </v-row>
  </v-container>
</template>

<script>
import L from 'leaflet';
import leafletImage from 'leaflet-image';

export default {
  name: 'LMap',
  props: {
    address: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      map: null,
      marker: null,
      direccion: '',
      provinciaSeleccionada: '',
      direccionSeleccionada: '',
      lat: null,
      lng: null,
      latInput: '',
      lngInput: '',
      url: '',
      departamentos: [],
      provinciasFiltradas: [],
      limitesDepartamentos: {},
      boundaryRectangle: null,
      geojsonLayer: null,
      geojsonData: null,
      ubicaciones: [],
      departamentoIdNombreMap: {}
    };
  },
  computed: {
    departamentoSeleccionado() {
      return this.departamentoIdNombreMap[this.address.city_address_id] || '';
    }
  },
  watch: {
    departamentoSeleccionado(nuevo) {
      if (nuevo) {
        this.filtrarProvincias(nuevo);
        // Solo centrar si NO hay coordenadas previas
        if (!this.address.latitude || !this.address.longitude) {
          this.centrarEnDepartamento();
          this.buscarDireccion();
        }
      }
    },
    provinciaSeleccionada() {
      this.buscarDireccion();
    }
  },
  mounted() {
    this.$nextTick(async () => {
      this.inicializarMapa();
      await this.cargarUbicaciones();
      await this.cargarGeoJSON();

      // Usar coordenadas del padre si existen
      if (this.address.latitude && this.address.longitude) {
        this.latInput = this.address.latitude;
        this.lngInput = this.address.longitude;
        this.centrarPorCoordenadas();
      }
    });
  },
  methods: {
    inicializarMapa() {
      this.map = L.map('map').setView([-16.5, -68.15], 6);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
      }).addTo(this.map);

      // // Capa base OSM normal
      // const osm = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      //   attribution: "© OpenStreetMap contributors"
      // });

      // // Capa satélite de ESRI
      // const esriSat = L.tileLayer(
      //   "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}", 
      //   {
      //     attribution: "Tiles © Esri & the GIS User Community"
      //   }
      // );

      // this.map = L.map('map').setView([-16.5, -68.15], 6);
      // L.control.layers({
      //   "OpenStreetMap": osm,
      //   "Satélite (Esri)": esriSat
      // }).addTo(this.map);

      this.map.on('click', async (e) => {
        const { lat, lng } = e.latlng;
        const limites = this.limitesDepartamentos[this.departamentoSeleccionado];
        if (limites) {
          const dentro = (
            lat >= limites.minLat &&
            lat <= limites.maxLat &&
            lng >= limites.minLng &&
            lng <= limites.maxLng
          );
          if (!dentro) {
            this.toastr.error('Ubicación fuera del departamento seleccionado');
            return;
          }
        }
        this.lat = lat;
        this.lng = lng;
        this.latInput = lat;
        this.lngInput = lng;
        this.colocarMarcador(lat, lng);
        await this.obtenerDireccion(lat, lng);
      });
    },

    colocarMarcador(lat, lng) {
      if (this.marker) {
        this.marker.setLatLng([lat, lng]);
      } else {
        this.marker = L.marker([lat, lng]).addTo(this.map);
      }
      this.address.latitude = lat;
      this.address.longitude = lng
      //this.address.image = 'miimagen'; // Limpiar imagen al mover el marcador
      if (!this.map) return;


      leafletImage(this.map, async (e, canvas) => {
        if (e) {
          console.error('Error al capturar el mapa:', e);
          return;
        }

        const imgData = canvas.toDataURL('image/png');
        this.address.image = imgData; })
      },
    async obtenerDireccion(lat, lng) {
      const url = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`;
      const response = await fetch(url);
      const data = await response.json();
      this.direccionSeleccionada = data.display_name || 'Dirección no disponible';
    },

    async buscarDireccion() {
      if (!this.departamentoSeleccionado) return;
      const partes = [];
      if (this.direccion) partes.push(this.direccion);
      if (this.provinciaSeleccionada) {
        const partesProv = this.provinciaSeleccionada.split(',').map(p => p.trim()).reverse();
        partes.push(...partesProv);
      }
      partes.push(this.departamentoSeleccionado);
      partes.push('Bolivia');

      const consulta = partes.join(', ');
      const limites = this.limitesDepartamentos[this.departamentoSeleccionado];
      let viewbox = '';
      if (limites) {
        const { minLat, maxLat, minLng, maxLng } = limites;
        viewbox = `&viewbox=${minLng},${maxLat},${maxLng},${minLat}&bounded=1`;
      }

      const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(consulta)}&format=json&addressdetails=1&limit=1${viewbox}`;
      const response = await fetch(url);

      const data = await response.json();
      if (data.length > 0) {
        const ubicacion = data[0];
        this.lat = parseFloat(ubicacion.lat);
        this.lng = parseFloat(ubicacion.lon);
        this.latInput = this.lat;
        this.lngInput = this.lng;
        this.direccionSeleccionada = ubicacion.display_name;
        this.map.setView([this.lat, this.lng], 16);
        this.colocarMarcador(this.lat, this.lng);
      } else {
        this.toastr.error('Dirección no encontrada dentro del departamento seleccionado');
      }
    },

    centrarPorCoordenadas() {
      console.log('entro1')
      if (!this.latInput || !this.lngInput) return;
      this.lat = parseFloat(this.latInput);
      this.lng = parseFloat(this.lngInput);
      this.map.setView([this.lat, this.lng], 16);
      this.colocarMarcador(this.lat, this.lng);
      this.obtenerDireccion(this.lat, this.lng);
      console.log('entro2')
    },

    centrarEnDepartamento() {
      const limites = this.limitesDepartamentos[this.departamentoSeleccionado];
      if (!limites) return;

      const bounds = [
        [limites.minLat, limites.minLng],
        [limites.maxLat, limites.maxLng]
      ];
      this.map.fitBounds(bounds);

      if (this.boundaryRectangle) this.map.removeLayer(this.boundaryRectangle);
      this.boundaryRectangle = L.rectangle(bounds, {
        color: "#FF7800",
        weight: 1,
        fillOpacity: 0.1
      }).addTo(this.map);

      if (this.geojsonLayer) this.map.removeLayer(this.geojsonLayer);
      this.geojsonLayer = L.geoJSON(this.geojsonData, {
        filter: f => f.properties.shapeName === this.departamentoSeleccionado,
        style: {
          color: "#0066CC",
          weight: 2,
          fillOpacity: 0.05
        }
      }).addTo(this.map);
    },

    async cargarGeoJSON() {
      try {
        const response = await fetch('/geojson/geoBoundaries-BOL-ADM1.geojson');
        const geojson = await response.json();
        this.geojsonData = geojson;
        this.limitesDepartamentos = this.extraerLimitesDepartamentos(geojson);
        this.departamentoIdNombreMap = this.crearMapaIdNombre(geojson);
        L.geoJSON(geojson, {
          style: {
            color: "#999",
            weight: 1,
            fillOpacity: 0.05
          }
        }).addTo(this.map);
      } catch (error) {
        console.error('Error cargando GeoJSON:', error);
      }
    },

    crearMapaIdNombre(geojson) {
      const mapa = {};
      geojson.features.forEach(f => {
        if (f.properties && f.properties.id && f.properties.shapeName) {
          mapa[f.properties.id] = f.properties.shapeName;
        }
      });
      return mapa;
    },

    extraerLimitesDepartamentos(geojson) {
      const limites = {};
      geojson.features.forEach((feature) => {
        const nombre = feature.properties.shapeName;
        const coords = feature.geometry.coordinates;
        let puntos = [];
        if (feature.geometry.type === 'Polygon') {
          puntos = coords[0];
        } else if (feature.geometry.type === 'MultiPolygon') {
          coords.forEach(polygon => {
            puntos = puntos.concat(polygon[0]);
          });
        }
        const lats = puntos.map(p => p[1]);
        const lngs = puntos.map(p => p[0]);
        limites[nombre] = {
          minLat: Math.min(...lats),
          maxLat: Math.max(...lats),
          minLng: Math.min(...lngs),
          maxLng: Math.max(...lngs)
        };
      });
      return limites;
    },

    async cargarUbicaciones() {
      try {
        const response = await fetch('/geojson/bolivia.json');
        this.ubicaciones = await response.json();
      } catch (error) {
        console.error('Error cargando ubicaciones:', error);
      }
    },

    filtrarProvincias(dep) {
      if (!dep || !this.ubicaciones.length) {
        this.provinciasFiltradas = [];
        return;
      }

      const provincias = this.ubicaciones
        .filter(u => u.dep === this.address.city_address_id)
        .map(u => u.prov_mun);

      this.provinciasFiltradas = [...new Set(provincias)];
    },

    procesarURL() {
      const coordenadas = this.extraerCoordenadasDesdeURL(this.url);
      if (!coordenadas) {
        this.toastr.error('No se pudieron extraer coordenadas de la URL.');
        return;
      }
      const [lat, lng] = coordenadas;
      this.map.setView([lat, lng], 17);
      this.colocarMarcador(lat, lng);
      this.obtenerDireccion(lat, lng);
    },

    extraerCoordenadasDesdeURL(url) {
      const regex = /@(-?\d+\.\d+),(-?\d+\.\d+)/;
      const match = url.match(regex);
      if (match) {
        return [parseFloat(match[1]), parseFloat(match[2])];
      }
      return null;
    },

    limpiarCampos() {
      this.provinciaSeleccionada = null;
      this.direccion = '';
    },
    limpiarCamposGoogleMaps() {
      this.url = '';
    },
    imprimirComoImagen() {
      if (!this.map) return;
      this.centrarPorCoordenadas()

      leafletImage(this.map, async (e, canvas) => {
        if (e) {
          console.error('Error al capturar el mapa:', e);
          return;
        }

        const imgData = canvas.toDataURL('image/png');
        this.address.image = imgData; // Guardar imagen en el objeto address
      });

    }
  }
};
</script>