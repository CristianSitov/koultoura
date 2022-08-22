<template>
    <div class="snap-start scroll-m-0 snap-both">
        <div class="h-auto">
            <div id="mapContainer"></div>
        </div>
    </div>
</template>

<script>
import mapboxgl from "mapbox-gl";

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
                [21.1900, 45.7300],
                [21.2900, 45.7800],
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
                        title: 'Mapbox',
                        description: 'Washington, D.C.'
                    }
                },
                {
                    type: 'Feature',
                    geometry: {
                        type: 'Point',
                        coordinates: [21.2470, 45.7605]
                    },
                    properties: {
                        title: 'Mapbox',
                        description: 'San Francisco, California'
                    }
                }
            ]
        }
        for (const feature of geojson.features) {
            const el = document.createElement('div');
            el.className = 'marker';
            new mapboxgl.Marker(el).setLngLat(feature.geometry.coordinates).addTo(this.map);
        }
        this.map.on("load", function(e) {
            new mapboxgl.Marker()
                .setLngLat([21.2220, 45.7353])
                .addTo(this.map);
            new mapboxgl.Marker()
                .setLngLat([21.2520, 45.7653])
                .addTo(this.map);
        });
    },
};
</script>
