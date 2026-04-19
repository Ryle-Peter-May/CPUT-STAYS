<?php
session_start();
$mysqli = require __DIR__ . "/database.php";

// Fetch accommodations and limiting it to display only 3 for homepage preview to make it less cramped
$sql = "SELECT a.AccommodationID, a.Name, a.Address, a.ContactNum, a.Amenities,
           r.RmType, r.PricePerRmType, r.AvailableRms
        FROM accommodation a
        JOIN rooms r ON a.AccommodationID = r.AccommodationID
        where r.AvailableRms > 0 and a.AccommodationID in(
        select AccommodationID from (Select distinct AccommodationID from rooms where AvailableRms > 0 order by AccommodationID Limit 3) as temp
        ) order by a.AccommodationID";
$result = $mysqli->query($sql);

$accommodations = [];
while ($row = $result->fetch_assoc()) {
    $id = $row['AccommodationID'];
    if (!isset($accommodations[$id])) {
        $accommodations[$id] = [
            'Name' => $row['Name'],
            'Address' => $row['Address'],
            'ContactNum' => $row['ContactNum'],
            'Amenities' => $row['Amenities'],
            'Rooms' => []
        ];
    }
    if ($row['RmType']) {
        $accommodations[$id]['Rooms'][] = [
            'RmType' => $row['RmType'],
            'Price' => $row['PricePerRmType'],
            'Available' => $row['AvailableRms']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Accommodation - Home</title>
  <link rel="stylesheet" href="homepage.css"/>
  <style>
    /* Search Overlay */
    #searchOverlay {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.95);
      z-index: 10002;
      justify-content: flex-start;
      align-items: center;
      flex-direction: column;
      padding-top: 80px;
      color: white;
      overflow-y: auto;
      position: fixed;
    }

    body.no-scroll{
      overflow: hidden;
    }

    #searchOverlay input {
      padding: 15px;
      width: 80%;
      max-width: 500px;
      border-radius: 5px;
      border: none;
      font-size: 18px;
    }
    #searchResults {
      margin-top: 30px;
      width: 80%;
      max-width: 1000px;
      padding-bottom: 100px;
    }
    #searchOverlay .close-btn {
      position: absolute;
      top: 20px; right: 30px;
      background: none;
      border: none;
      color: white;
      font-size: 28px;
      cursor: pointer;
    }

    /* Cards */
    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1.5em;
    }
    .card {
      background: white;
      padding: 1em;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      text-align: center;
    }
    .card img {
      max-width: 100%;
      border-radius: 10px;
      height: 150px;
      object-fit: cover;
    }

    /* Button */
    .cta-button {
      display: inline-block;
      margin-top: 10px;
      padding: 8px 12px;
      background: #0073e6;
      color: white;
      text-decoration: none;
      border-radius: 5px;
    }
    .cta-button:hover {
      background: #005bb5;
    }

    /* Modal */
    .modal {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) scale(0);
      transition: 200ms ease-in-out;
      border: 1px solid black;
      border-radius: 10px;
      z-index: 10003;
      background-color: white;
      width: 500px;
      max-width: 80%;
    }
    .modal.active {
      transform: translate(-50%, -50%) scale(1);
    }
    .modal-header {
      padding: 10px 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid black;
    }
    .modal-header .title {
      font-size: 1.25rem;
      font-weight: bold;
    }
    .modal-header .close-button {
      cursor: pointer;
      border: none;
      outline: none;
      background: none;
      font-size: 1.25rem;
      font-weight: bold;
    }
    .modal-body {
      padding: 10px 15px;
    }
    #overlay {
      position: fixed;
      opacity: 0;
      transition: 200ms ease-in-out;
      top: 0; left: 0; right: 0; bottom: 0;
      background-color: rgba(0,0,0,0.5);
      pointer-events: none;
      z-index: 10000;
    }
    #overlay.active {
      opacity: 1;
      pointer-events: all;
    }

    #searchOverlay .card{
      color: black;
      background: white;
    }

    #searchOverlay .card p,
    #searchOverlay .card h3{
      color: black;
    }

    #searchOverlay .modal{
      color: black;
      background: white;
    }

    #searchOverlay .modal p,
    #searchOverlay .modal h3,
    #searchOverlay .modal h4,
    #searchOverlay .modal li{
      color: black;
    }
    
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <a href="index.html" style="text-decoration: none; color: white;">
        <img src="logo.jpg" alt="cput logo" style="height: 40px; margin-right: 10px; vertical-align: middle;">
        CPUT STAYS
      </a>
    </div>
    <nav>
      <a href="profile.php">Profile</a>
      <!-- <a href="logout.php">Logout</a> -->
    </nav>
  </header>

  <main>
    <section class="hero-home">
      <h1>Welcome to CPUT STAYS</h1>

      <!-- Search Bar -->
      <div style="text-align: center; margin-top: 15px;">
        <input type="text" placeholder="Search accommodations..." onclick="openSearch()" readonly
        style="padding: 10px; width:300px; border-radius:5px; border: 1px solid #ccc; cursor: pointer;">
      </div>  
    </section>

    <!-- Featured Accommodations -->
    <section class="featured">
      <h2>Available Accommodations</h2>
      <div class="cards">
        <?php if (!empty($accommodations)): ?>
          <?php foreach ($accommodations as $id => $accom): ?>
            <?php $modalId = "modal-" . $id; ?>
            <div class="card">
              <img src="room1.jpg" alt="<?= htmlspecialchars($accom['Name']) ?>" />
              <h3><?= htmlspecialchars($accom['Name']) ?></h3>
              <p><strong>Address:</strong> <?= htmlspecialchars($accom['Address']) ?></p>
              <p><strong>Contact:</strong> <?= htmlspecialchars($accom['ContactNum']) ?></p>
              <!-- <p><strong>Amenities:</strong> <?= htmlspecialchars($accom['Amenities']) ?></p> -->

              <button data-modal-target="#<?= $modalId ?>">View More Details</button>
            </div>

            <!-- Modal -->
            <div class="modal" id="<?= $modalId ?>">
              <div class="modal-header">
                <div class="title"><?= htmlspecialchars($accom['Name']) ?></div>
                <button data-close-button class="close-button">&times;</button>
              </div>
              <div class="modal-body">
                <p><strong>Address:</strong> <?= htmlspecialchars($accom['Address']) ?></p>
                <p><strong>Contact:</strong> <?= htmlspecialchars($accom['ContactNum']) ?></p>
                <p><strong>Amenities:</strong> <?= htmlspecialchars($accom['Amenities']) ?></p>
                <?php if (!empty($accom['Rooms'])): ?>
                  <h4>Room Types</h4>
                  <ul>
                    <?php foreach ($accom['Rooms'] as $room): ?>
                      <li>
                        <?= htmlspecialchars($room['RmType']) ?> -
                        R<?= number_format($room['Price'], 2) ?> 
                        (<?= $room['Available'] ?> available)
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
                <a href="booking.php?accommodation_id=<?= $id ?>" class="cta-button">Book Now</a>
              </div>
            </div>
          <?php endforeach; ?>
          <!-- <div id="overlay"></div> -->
        <?php else: ?>
          <p>No accommodations available yet.</p>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <footer>
    <p>© 2025 CPUT STAYS. All rights reserved.</p>
  </footer>

  <!-- Search Overlay -->
  <div id="searchOverlay">
    <button onclick="closeSearch()" class="close-btn">X</button>
    <input type="text" id="searchInput" placeholder="Search accommodations...">
    <div id="searchResults"></div>
  </div>
  <div id="overlay"></div>
  <script>
    const overlay = document.getElementById('overlay');

    function openModal(modal) {
      if (!modal) return;
      modal.classList.add('active');
      overlay.classList.add('active');
    }

    function closeModal(modal) {
      if (!modal) return;
      modal.classList.remove('active');
      overlay.classList.remove('active');
    }

    // Stoping clicks inside modal from closing it
    function stopModalInnerClicks() {
      document.querySelectorAll('.modal').forEach(modal => {
        modal.onclick = e => e.stopPropagation();
      });
    }
    stopModalInnerClicks();

    function closeAllActiveModals() {
      document.querySelectorAll('.modal.active').forEach(m => closeModal(m));
    }

    // Opening and closing modal events
    document.addEventListener("click", function(e) {
      const modalTarget = e.target.closest("[data-modal-target]");
      const closeButton = e.target.closest("[data-close-button]");
      const activeModal = document.querySelector('.modal.active');

      if (modalTarget) {
        const modal = document.querySelector(modalTarget.dataset.modalTarget);
        openModal(modal);
      } else if (closeButton) {
        const modal = closeButton.closest(".modal");
        closeModal(modal);
      } else if (activeModal && !activeModal.contains(e.target)) {
        // click outside the modal to close it
        closeAllActiveModals();
      }
    });

    overlay.addEventListener("click", closeAllActiveModals);
    document.addEventListener("keydown", e => { if (e.key === "Escape") closeAllActiveModals(); });

    // === SEARCH OVERLAY ===
    function openSearch() {
      document.getElementById("searchOverlay").style.display = "flex";
      document.body.classList.add("no-scroll");
      document.getElementById("searchInput").focus();
      loadAccommodations("");
    }

    function closeSearch() {
      document.getElementById("searchOverlay").style.display = "none";
      document.body.classList.remove("no-scroll");
      document.getElementById("searchResults").innerHTML = "";
      document.getElementById("searchInput").value = "";
    }

    function loadAccommodations(query) {
      const resultsBox = document.getElementById("searchResults");

      fetch("search-api.php?q=" + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
          if (data.length === 0) {
            resultsBox.innerHTML = "<p style='color:white;'>No accommodations found.</p>";
            return;
          }

          resultsBox.innerHTML = `
            <div class="cards">
              ${data.map(item => {
                const modalId = "search-modal-" + item.AccommodationID;
                return `
                  <div class="card">
                    <img src="room1.jpg" alt="${item.Name}">
                    <h3>${item.Name}</h3>
                    <p><strong>Address:</strong> ${item.Address}</p>
                    <p><strong>Contact:</strong> ${item.ContactNum}</p>
                    <p><strong>Amenities:</strong> ${item.Amenities}</p>
                    <button data-modal-target="#${modalId}">View More Details</button>
                  </div>

                  <div class="modal" id="${modalId}">
                    <div class="modal-header">
                      <div class="title">${item.Name}</div>
                      <button data-close-button class="close-button">&times;</button>
                    </div>
                    <div class="modal-body">
                      <p><strong>Address:</strong> ${item.Address}</p>
                      <p><strong>Contact:</strong> ${item.ContactNum}</p>
                      <p><strong>Amenities:</strong> ${item.Amenities}</p>
                      <?php if (!empty($accom['Rooms'])): ?>
                        <h4>Room Types</h4>
                        <ul>
                          <?php foreach ($accom['Rooms'] as $room): ?>
                            <li>
                              <?= htmlspecialchars($room['RmType']) ?> -
                              R<?= number_format($room['Price'], 2) ?> 
                              (<?= $room['Available'] ?> available)
                            </li>
                          <?php endforeach; ?>
                        </ul>
                      <?php endif; ?>
                      <a href="booking.php?accommodation_id=${item.AccommodationID}" class="cta-button">Book Now</a>
                    </div>
                  </div>
                `;
              }).join("")}
            </div>
          `;

          stopModalInnerClicks();
        });
    }

    document.getElementById("searchInput").addEventListener("input", function() {
      loadAccommodations(this.value.trim());
    });
  </script>
</body>
</html>
