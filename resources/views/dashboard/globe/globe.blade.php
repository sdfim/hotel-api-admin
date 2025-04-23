@extends('layouts.master')

<head>
    <meta charset="utf-8">
    <!-- Include the CesiumJS JavaScript and CSS files -->
    <script src="https://cesium.com/downloads/cesiumjs/releases/1.125/Build/Cesium/Cesium.js"></script>
    <link href="https://cesium.com/downloads/cesiumjs/releases/1.125/Build/Cesium/Widgets/widgets.css" rel="stylesheet">
    <style>
        #cesiumContainer {
            width: 100%;
            height: 100vh;
            margin: 0;
            padding: 0;
        }

        .cesium-viewer-toolbar {
            top: 70px !important;
        }

        #showHotels {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #showHotels {
            top: 120px;
            display: none;
        }
    </style>
    <title>Search Hotels</title>
</head>
<body>
<div id="cesiumContainer"></div>
<button id="showHotels">Show Hotels</button>
<script type="module">
    document.addEventListener('DOMContentLoaded', function () {
        Cesium.Ion.defaultAccessToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiJhNWIxYmY5YS05NDEzLTQ1YzgtOWQ3Mi1iOGJkMjNlM2NlZWIiLCJpZCI6MjcxOTA4LCJpYXQiOjE3MzgwNjIyNjN9.PDoYJUNNCrh6F0PfyPET-kJ8AM4pyf3P-ihICNcZKeo';

        const viewer = new Cesium.Viewer('cesiumContainer', {
            terrain: Cesium.Terrain.fromWorldTerrain(),
        });

        viewer.camera.flyTo({
            destination: Cesium.Cartesian3.fromDegrees(-122.4175, 37.655, 400),
            orientation: {
                heading: Cesium.Math.toRadians(-10.0),
                pitch: Cesium.Math.toRadians(-15.0),
            }
        });

        let polygonPositions = []; // Stores latitude and longitude
        let lastPolygonEntity = null; // Reference to the last created polygon

        const handler = new Cesium.ScreenSpaceEventHandler(viewer.scene.canvas);

        // Click processing to add a point
        handler.setInputAction(function (event) {
            const earthPosition = viewer.scene.pickPosition(event.position);

            if (Cesium.defined(earthPosition)) {
                const cartographic = Cesium.Cartographic.fromCartesian(earthPosition);
                const lat = Cesium.Math.toDegrees(cartographic.latitude);
                const lng = Cesium.Math.toDegrees(cartographic.longitude);

                // Add coordinates (latitude and longitude) to the array
                polygonPositions.push(lng, lat);

                // Display the point on the map
                viewer.entities.add({
                    position: earthPosition,
                    point: {
                        pixelSize: 5,
                        color: Cesium.Color.RED,
                    },
                });

                // Draw a polygon if there are 3 or more points
                if (polygonPositions.length >= 6) { // 6 elements = 3 points
                    if (lastPolygonEntity) {
                        // Delete the previous polygon
                        viewer.entities.remove(lastPolygonEntity);
                    }

                    // Create a new polygon
                    lastPolygonEntity = viewer.entities.add({
                        polygon: {
                            hierarchy: Cesium.Cartesian3.fromDegreesArray(polygonPositions),
                            material: Cesium.Color.BLUE.withAlpha(0.5), // Полупрозрачный синий цвет
                            outline: true, // Границы полигона
                            outlineColor: Cesium.Color.YELLOW, // Желтые границы
                        },
                    });

                    // Show “Show Hotels” button
                    document.getElementById('showHotels').style.display = 'block';
                }
            }
        }, Cesium.ScreenSpaceEventType.LEFT_CLICK);

        document.getElementById('showHotels').addEventListener('click', function () {
            console.log('polygonPositions:', polygonPositions);
            const Hotels = polygonPositions.join(';');
            window.location.href = `{{ route('properties.index') }}?polygon=${Hotels}`;
        });
    });
</script>
</div>
</body>
</html>
