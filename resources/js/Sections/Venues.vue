<template>
    <div class="snap-start scroll-pt-60 snap-both">
        <div id="venues" class="w-full">
            <div id="mapContainer"></div>
        </div>
    </div>
</template>

<script>
import mapboxgl from "mapbox-gl";
// https://docs.mapbox.com/help/tutorials/custom-markers-gl-js/
export default {
    name: "BaseMap",
    data() {
        return {
            map: {},
        };
    },
    mounted() {
        mapboxgl.accessToken = 'pk.eyJ1IjoiY3Jpc3RpYW5zaXRvdiIsImEiOiJMcl9sX2F3In0.0OVCrFfICO66Rs-v3tBc0g';
        this.map = new mapboxgl.Map({
            container: "mapContainer",
            style: "mapbox://styles/cristiansitov/cl74kh88m003b14p7z3lucmsu",
            center: [21.2420, 45.7400],
            zoom: 10,
            // dragPan: false,
            scrollZoom: false,
            boxZoom: false,
            doubleClickZoom: false,
            maxBounds: [
                [21.1800, 45.7200],
                [21.3000, 45.7900],
            ],
        });

        const geojson = {
            'en': {
                type: 'FeatureCollection',
                features: [
                    {
                        type: 'Feature',
                        geometry: {
                            type: 'Point',
                            coordinates: [21.2320, 45.7480]
                        },
                        properties: {
                            title: '<span class="font-bold text-md">Aula Magna & Amphitheater 01</span><br><span class="font-bold font-md">West University of Timișoara</span><br><br>',
                            description: '4, Vasile Pârvan blvd.<br><a class="font-bold" href="https://goo.gl/maps/V8jY8nx4AyqFWx7JA" target="_blank">👉 Directions</a>'
                        }
                    },
                    {
                        type: 'Feature',
                        geometry: {
                            type: 'Point',
                            coordinates: [21.2530, 45.7620]
                        },
                        properties: {
                            title: '<span class="font-bold text-md">Multifunctional Hall</span><br><span class="font-bold font-md">FABER</span><br><br>',
                            description: '4-5, Peneș Curcanul str.<br><a class="font-bold" href="https://goo.gl/maps/MXi74ybfZAReWdin8" target="_blank">👉 Directions</a>'
                        }
                    }
                ]
            },
            'ro': {
                type: 'FeatureCollection',
                features: [
                    {
                        type: 'Feature',
                        geometry: {
                            type: 'Point',
                            coordinates: [21.2320, 45.7480]
                        },
                        properties: {
                            title: '<span class="font-bold text-md">Aula Magna & Amfiteatru 01</span><br><span class="font-bold font-md">Universitatea de Vest din Timișoara</span><br><br>',
                            description: 'Bd. Vasile Pârvan nr. 4<br><a class="font-bold" href="https://goo.gl/maps/V8jY8nx4AyqFWx7JA" target="_blank">👉 Indicații de orientare</a>'
                        }
                    },
                    {
                        type: 'Feature',
                        geometry: {
                            type: 'Point',
                            coordinates: [21.2530, 45.7620]
                        },
                        properties: {
                            title: '<span class="font-bold text-md">Sala Multifuncțională</span><br><span class="font-bold font-md">FABER</span><br><br>',
                            description: 'str. Peneș Curcanul, nr. 4-5<br><a class="font-bold" href="https://goo.gl/maps/MXi74ybfZAReWdin8" target="_blank">👉 Indicații de orientare</a>'
                        }
                    }
                ]
            },
        }

        for (const feature of geojson[this.$page.props.locale].features) {
            const el = document.createElement('div');
            el.className = 'marker';
            new mapboxgl
                .Marker(el)
                .setLngLat(feature.geometry.coordinates)
                .setPopup(
                    new mapboxgl.Popup({
                        offset: 25,
                        closeOnClick: false,
                        keepInView: true,
                        closeButton: false,
                    })
                        .setHTML(
                            `<h3>${feature.properties.title}</h3><p>${feature.properties.description}</p>`
                        )
                )
                .addTo(this.map)
                .togglePopup();
        }
    },
};
</script>
