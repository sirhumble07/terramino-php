<!DOCTYPE html>
<html>

<head>
  <title>Terramino</title>
  <link rel="icon" href="https://www.terraform.io/favicon.ico" type="image/x-icon" />
  <style>
    html, body { height: 100%; margin: 0; }
    body {
      background-image: url("https://github.com/hashicorp/learn-terramino/raw/master/background.png");
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-family: Arial, Helvetica, sans-serif;
    }
    h1 { font-family: Impact, Charcoal, sans-serif; }
    canvas { border: 1px solid white; }

    .container { position: relative; margin: 0 auto; }
    .content { position: relative; left: 0; top: 0; }
    .attribute-name { display: inline-block; font-weight: bold; width: 10em; }

    /* NEW: metadata panel */
    #metadata-box {
      background: rgba(0,0,0,0.6);
      padding: 10px;
      border-radius: 8px;
      margin-top: 10px;
      max-width: 600px;
      white-space: pre-wrap;
      font-size: 14px;
      display: none;
      overflow-x: auto;
    }

    #toggle-btn {
      background-color: #444;
      color: #fff;
      padding: 6px 10px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin-bottom: 8px;
    }
  </style>
</head>

<?php
// SAFELY GET METADATA FROM AZURE IMDS
function get_imds($path) {
    $url = "http://169.254.169.254/metadata/" . $path . "?api-version=2021-02-01";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => ['Metadata:true'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 1,
        CURLOPT_TIMEOUT => 2
    ]);

    $output = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http === 200 && $output) {
        return $output;
    }
    return null;
}

// BASIC FIELDS
$vm_name = get_imds("instance/compute/name") ?: "N/A";
$zone = get_imds("instance/compute/zone") ?: "N/A";
$resource_id = get_imds("instance/compute/resourceId") ?: "N/A";

// FULL METADATA (ALL JSON)
$full_metadata = get_imds("instance") ?: json_encode(["error" => "Metadata unavailable"], JSON_PRETTY_PRINT);
$full_metadata_pretty = json_encode(json_decode($full_metadata, true), JSON_PRETTY_PRINT);
?>

<body>
  <div class="container">
    <div class="content">
      <h1>Terramino</h1>

      <p><span class="attribute-name">VM Name:</span><code><?= htmlspecialchars($vm_name) ?></code></p>
      <p><span class="attribute-name">Instance ID:</span><code><?= htmlspecialchars($resource_id) ?></code></p>
      <p><span class="attribute-name">Availability Zone:</span><code><?= htmlspecialchars($zone) ?></code></p>

      <!-- NEW: Full metadata toggle -->
      <button id="toggle-btn" onclick="toggleMeta()">Show Full Metadata</button>
      <div id="metadata-box"><?= htmlspecialchars($full_metadata_pretty) ?></div>

      <p>Use left and right arrow keys to move blocks.<br />Use up arrow key to flip block.</p>
    </div>

    <div class="content">
      <canvas width="320" height="640" id="game"></canvas>
    </div>
  </div>

  <script>
    // Metadata Collapse Toggle
    function toggleMeta() {
      const box = document.getElementById("metadata-box");
      const btn = document.getElementById("toggle-btn");

      if (box.style.display === "none") {
        box.style.display = "block";
        btn.textContent = "Hide Metadata";
      } else {
        box.style.display = "none";
        btn.textContent = "Show Full Metadata";
      }
    }
  </script>
</body>
</html>
  

