<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEM MONITORING SKUTER LISTRIK SEWA</title>

    <!-- Add link to Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Add link to Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css">

    <!-- Include Paho MQTT JavaScript library from the CDN -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.2/mqttws31.min.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }
        h1, h2 {
            color: #343a40;
        }
        #map {
            height: 500px;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .modal-body {
            text-align: center;
            font-size: 1.2em;
            color: #dc3545;
        }
    </style>

    <script>
        let map;
        let markers = {}; // Object to store markers by skuterId
        let client;

        function connectClient() {
            var clientId = "CapstoneYAARClient-" + new Date().getTime();
            client = new Paho.MQTT.Client("broker.hivemq.com", 8000, clientId);

            client.onConnectionLost = onConnectionLost;
            client.onMessageArrived = onMessageArrived;

            client.connect({onSuccess: onConnect, onFailure: onFailure});
        }

        function onConnect() {
            console.log("Connected");
            client.subscribe("test/topic");
        }

        function onConnectionLost(responseObject) {
            if (responseObject.errorCode !== 0) {
                console.log("onConnectionLost:" + responseObject.errorMessage);
                setTimeout(connectClient, 5000);
            }
        }

        function onFailure(responseObject) {
            console.log("Failed to connect: " + responseObject.errorMessage);
            setTimeout(connectClient, 5000);
        }

        function onMessageArrived(message) {
            console.log("onMessageArrived:" + message.payloadString);

            var parts = message.payloadString.split('|');
            var skuterId = parts[0];
            var lat = parseFloat(parts[1]);
            var lon = parseFloat(parts[2]);
            var buttonStatus = parts[3];
            var speed = parseFloat(parts[4]);
            var timer = parts[5];

            if (!isNaN(lat) && !isNaN(lon)) {
                if (!map) {
                    map = L.map('map').setView([lat, lon], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);
                }

                if (markers[skuterId]) {
                    markers[skuterId].setLatLng([lat, lon]).bindPopup(skuterId).openPopup();
                } else {
                    markers[skuterId] = L.marker([lat, lon]).addTo(map).bindPopup(skuterId).openPopup();
                }
            } else {
                console.error("Invalid latitude or longitude values");
            }

            if (!isNaN(lat) && !isNaN(lon)) {
                var table = document.getElementById("latestData");
                var existingRow = null;
                for (var i = 0, row; row = table.rows[i]; i++) {
                    if (row.cells[0].innerText === skuterId) {
                        existingRow = row;
                        break;
                    }
                }

                if (existingRow) {
                    existingRow.cells[1].innerText = buttonStatus;
                    existingRow.cells[2].innerText = lat;
                    existingRow.cells[3].innerText = lon;
                    existingRow.cells[4].innerText = speed;
                    existingRow.cells[5].innerText = timer;
                } else {
                    var newRow = table.insertRow();
                    newRow.innerHTML = `<td>${skuterId}</td><td>${buttonStatus}</td><td>${lat}</td><td>${lon}</td><td>${speed}</td><td>${timer}</td>`;
                }

                if (buttonStatus === "Help Button Pressed") {
                    $('#helpModal').modal('show');
                    $('#helpModal .modal-body').text(`${skuterId} membutuhkan bantuan!`);
                }
            } else {
                console.log("No skuter data available.");
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            connectClient();
        });
    </script>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">SISTEM MONITORING SKUTER LISTRIK SEWA</h1>
        <div id="map" class="mb-4"></div>
        <h2 class="mt-4">Data Monitoring</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>SkuterId</th>
                        <th>Status Button Help</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Kecepatan</th>
                        <th>Sisa Waktu</th>
                    </tr>
                </thead>
                <tbody id="latestData">
                    <!-- Data will be inserted here using JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel">Peringatan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Skuter membutuhkan bantuan!
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add link to Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Add link to Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
</body>
</html>
