@push('scripts')
<script
async
defer
src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&libraries=places,geometry&callback=initMap">
</script>

<script>
(function ($) {
    "use strict";

    let mapInstance;
    let currentShape = null;
    let drawingPoints = [];
    let drawingMarkers = [];
    let drawingPolyline = null;
    let isDrawingMode = false;

    let existingPolygon = @json($zone->locations ?? null);

    // Required by Google Maps callback
    window.initMap = function () {
        setupMap();
        setupCustomDrawingControls();
        setupGeolocation();
        loadExistingPolygon();
        searchBox();
    };

    function setupMap() {
        const startLocation = {
            lat: 20.5937,
            lng: 78.9629
        };

        mapInstance = new google.maps.Map(
            document.getElementById('map-container'),
            {
                zoom: 5,
                center: startLocation,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            }
        );
    }

    function setupCustomDrawingControls() {

        const controlDiv = document.createElement('div');
        controlDiv.style.cssText =
            'margin:10px;display:flex;gap:8px;';

        const drawBtn = document.createElement('button');
        drawBtn.type = 'button';
        drawBtn.id = 'draw-polygon-btn';
        drawBtn.innerHTML = '✏️ Draw Zone';
        drawBtn.style.cssText =
            'padding:8px 14px;background:#fff;border:2px solid #4285F4;border-radius:4px;cursor:pointer;font-weight:600;';

        const finishBtn = document.createElement('button');
        finishBtn.type = 'button';
        finishBtn.id = 'finish-polygon-btn';
        finishBtn.innerHTML = '✓ Finish';
        finishBtn.style.cssText =
            'padding:8px 14px;background:#34A853;border:2px solid #34A853;color:#fff;border-radius:4px;cursor:pointer;display:none;';

        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.id = 'clear-polygon-btn';
        clearBtn.innerHTML = '✕ Clear';
        clearBtn.style.cssText =
            'padding:8px 14px;background:#fff;border:2px solid #EA4335;color:#EA4335;border-radius:4px;cursor:pointer;';

        controlDiv.appendChild(drawBtn);
        controlDiv.appendChild(finishBtn);
        controlDiv.appendChild(clearBtn);

        mapInstance.controls[
            google.maps.ControlPosition.TOP_CENTER
        ].push(controlDiv);

        drawBtn.addEventListener('click', startDrawing);
        finishBtn.addEventListener('click', finishDrawing);
        clearBtn.addEventListener('click', clearDrawing);
    }

    function startDrawing() {

        clearDrawing();

        isDrawingMode = true;

        mapInstance.setOptions({
            draggableCursor: 'crosshair'
        });

        document.getElementById('draw-polygon-btn')
            .style.background = '#E8F0FE';

        document.getElementById('finish-polygon-btn')
            .style.display = 'inline-block';

        mapInstance.addListener('click', onMapClick);
    }

    function onMapClick(event) {

        if (!isDrawingMode) {
            return;
        }

        drawingPoints.push(event.latLng);

        const marker = new google.maps.Marker({
            position: event.latLng,
            map: mapInstance,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 5,
                fillColor: '#4285F4',
                fillOpacity: 1,
                strokeWeight: 2,
                strokeColor: '#ffffff'
            }
        });

        drawingMarkers.push(marker);

        if (drawingPolyline) {
            drawingPolyline.setMap(null);
        }

        drawingPolyline = new google.maps.Polyline({
            path: drawingPoints,
            strokeColor: '#4285F4',
            strokeWeight: 2,
            map: mapInstance
        });
    }

    function finishDrawing() {

        if (drawingPoints.length < 3) {
            alert('Please select at least 3 points.');
            return;
        }

        isDrawingMode = false;

        google.maps.event.clearListeners(
            mapInstance,
            'click'
        );

        mapInstance.setOptions({
            draggableCursor: null
        });

        document.getElementById('finish-polygon-btn')
            .style.display = 'none';

        document.getElementById('draw-polygon-btn')
            .style.background = '#fff';

        drawingMarkers.forEach(marker => marker.setMap(null));
        drawingMarkers = [];

        if (drawingPolyline) {
            drawingPolyline.setMap(null);
            drawingPolyline = null;
        }

        if (currentShape) {
            currentShape.setMap(null);
        }

        currentShape = new google.maps.Polygon({
            paths: drawingPoints,
            editable: true,
            draggable: false,
            strokeColor: '#4285F4',
            strokeOpacity: 1,
            strokeWeight: 2,
            fillColor: '#4285F4',
            fillOpacity: 0.30,
            map: mapInstance
        });

        savePolygonCoordinates();

        const path = currentShape.getPath();

        google.maps.event.addListener(
            path,
            'insert_at',
            savePolygonCoordinates
        );

        google.maps.event.addListener(
            path,
            'set_at',
            savePolygonCoordinates
        );

        google.maps.event.addListener(
            path,
            'remove_at',
            savePolygonCoordinates
        );
    }

    function savePolygonCoordinates() {

        if (!currentShape) {
            return;
        }

        let coordinates = [];

        currentShape.getPath().forEach(function(point) {

            coordinates.push({
                lat: point.lat(),
                lng: point.lng()
            });

        });

        if (
            coordinates.length > 2 &&
            (
                coordinates[0].lat !== coordinates[coordinates.length - 1].lat ||
                coordinates[0].lng !== coordinates[coordinates.length - 1].lng
            )
        ) {
            coordinates.push({
                lat: coordinates[0].lat,
                lng: coordinates[0].lng
            });
        }

        $('#place_points').val(
            JSON.stringify(coordinates)
        );
    }

    function clearDrawing() {

        isDrawingMode = false;

        google.maps.event.clearListeners(
            mapInstance,
            'click'
        );

        drawingPoints = [];

        drawingMarkers.forEach(marker => marker.setMap(null));
        drawingMarkers = [];

        if (drawingPolyline) {
            drawingPolyline.setMap(null);
            drawingPolyline = null;
        }

        if (currentShape) {
            currentShape.setMap(null);
            currentShape = null;
        }

        mapInstance.setOptions({
            draggableCursor: null
        });

        $('#place_points').val('');

        const finishBtn = document.getElementById(
            'finish-polygon-btn'
        );

        if (finishBtn) {
            finishBtn.style.display = 'none';
        }

        const drawBtn = document.getElementById(
            'draw-polygon-btn'
        );

        if (drawBtn) {
            drawBtn.style.background = '#fff';
        }
    }

    function loadExistingPolygon() {

        if (
            !existingPolygon ||
            existingPolygon.length === 0
        ) {
            return;
        }

        if (currentShape) {
            currentShape.setMap(null);
        }

        currentShape = new google.maps.Polygon({
            paths: existingPolygon,
            editable: true,
            draggable: false,
            strokeColor: '#4285F4',
            strokeOpacity: 1,
            strokeWeight: 2,
            fillColor: '#4285F4',
            fillOpacity: 0.30,
            map: mapInstance
        });

        const bounds = new google.maps.LatLngBounds();

        existingPolygon.forEach(function(point) {

            bounds.extend(
                new google.maps.LatLng(
                    point.lat,
                    point.lng
                )
            );

        });

        mapInstance.fitBounds(bounds);

        savePolygonCoordinates();

        const path = currentShape.getPath();

        google.maps.event.addListener(
            path,
            'insert_at',
            savePolygonCoordinates
        );

        google.maps.event.addListener(
            path,
            'set_at',
            savePolygonCoordinates
        );

        google.maps.event.addListener(
            path,
            'remove_at',
            savePolygonCoordinates
        );
    }

    function setupGeolocation() {

        if (!navigator.geolocation) {
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {

                const userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };

                mapInstance.setCenter(userLocation);
                mapInstance.setZoom(12);
            }
        );
    }

    function searchBox() {

        const input =
            document.getElementById('search-box');

        if (!input) {
            return;
        }

        const searchBox =
            new google.maps.places.SearchBox(input);

        mapInstance.addListener(
            'bounds_changed',
            function () {
                searchBox.setBounds(
                    mapInstance.getBounds()
                );
            }
        );

        searchBox.addListener(
            'places_changed',
            function () {

                const places =
                    searchBox.getPlaces();

                if (!places.length) {
                    return;
                }

                const bounds =
                    new google.maps.LatLngBounds();

                places.forEach(function(place) {

                    if (!place.geometry) {
                        return;
                    }

                    if (place.geometry.viewport) {
                        bounds.union(
                            place.geometry.viewport
                        );
                    } else {
                        bounds.extend(
                            place.geometry.location
                        );
                    }
                });

                mapInstance.fitBounds(bounds);
            }
        );
    }

    window.clearPolygon = clearDrawing;

})(jQuery);
</script>
@endpush
