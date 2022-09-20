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
            type: 'FeatureCollection',
            features: [
                {
                    type: 'Feature',
                    geometry: {
                        type: 'Point',
                        coordinates: [21.2320, 45.7480]
                    },
                    properties: {
                        title: 'Universitatea de Vest',
                        description: 'Bd. Vasile Pârvan nr. 4'
                    }
                },
                {
                    type: 'Feature',
                    geometry: {
                        type: 'Point',
                        coordinates: [21.2530, 45.7620]
                    },
                    properties: {
                        title: 'FABER',
                        description: 'str. Peneș Curcanul, nr. 4-5'
                    }
                }
            ]
        }
        for (const feature of geojson.features) {
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
