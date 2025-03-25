<?php
require 'vendor/autoload.php';

use Kreait\Firebase\Factory;
use Dotenv\Dotenv;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$serviceAccount = $_ENV['FIREBASE_SERVICE_ACCOUNT'];
$databaseUri = $_ENV['FIREBASE_DATABASE_URI'];

// Firebase connection

$factory = (new Factory)
    ->withServiceAccount($serviceAccount)
    ->withDatabaseUri($databaseUri);

$database = $factory->createDatabase();

// Fetch all food items
$foodItemsSnapshot = $database->getReference('Foods')->getSnapshot();
$allFoodItems = $foodItemsSnapshot->getValue();

$ordersRef = $database->getReference('orders')->getSnapshot();
$orders = $ordersRef->getValue();

$orders = array_filter($orders, function ($a) {
    return $a !== null && isset($a['userId']) && !empty($a['userId']);
});

// Filter out null or empty entries
$foodItems = array_filter($allFoodItems, function ($food) {
    // Check if the item is not null and has some meaningful content
    return $food !== null && isset($food['Title']) && !empty($food['Title']);
});

// Items per page
$itemsPerPage = 5;

// Get current page from URL (default to page 1)
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

// Total number of items
$totalItems = count($foodItems);

// Calculate total pages
$totalPages = ceil($totalItems / $itemsPerPage);

// Ensure page is within valid range
$page = min($page, $totalPages);

// Slice array for current page
$startIndex = ($page - 1) * $itemsPerPage;
$pagedFoodItems = array_slice($foodItems, $startIndex, $itemsPerPage, true);
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="./assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="./assets/img/favicon.png">
    <title>
        Admin Table FoodApp
    </title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="https://demos.creative-tim.com/soft-ui-dashboard/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="https://demos.creative-tim.com/soft-ui-dashboard/assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- CSS Files -->
    <link id="pagestyle" href="./assets/css/soft-ui-dashboard.css?v=1.1.0" rel="stylesheet" />
    <!-- Nepcha Analytics (nepcha.com) -->
    <!-- Nepcha is a easy-to-use web analytics. No cookies and fully compliant with GDPR, CCPA and PECR. -->
    <script defer data-site="YOUR_DOMAIN_HERE" src="https://api.nepcha.com/js/nepcha-analytics.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-database.js"></script>


    <style>
        td.description {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>

<body class="g-sidenav-show  bg-gray-100">
    <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 "
        id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
                aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand m-0" href=" https://demos.creative-tim.com/soft-ui-dashboard/pages/dashboard.html "
                target="_blank">
                <img src="./assets/img/logo-ct-dark.png" class="navbar-brand-img h-100" alt="main_logo">
                <span class="ms-1 font-weight-bold">Admin Table FoodApp</span>
            </a>
        </div>
        <hr class="horizontal dark mt-0">
        <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link  " href="./admin.php">
                        <div
                            class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <svg width="12px" height="12px" viewBox="0 0 45 40" version="1.1"
                                xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <title>shop </title>
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g transform="translate(-1716.000000, -439.000000)" fill="#FFFFFF"
                                        fill-rule="nonzero">
                                        <g transform="translate(1716.000000, 291.000000)">
                                            <g transform="translate(0.000000, 148.000000)">
                                                <path class="color-background opacity-6"
                                                    d="M46.7199583,10.7414583 L40.8449583,0.949791667 C40.4909749,0.360605034 39.8540131,0 39.1666667,0 L7.83333333,0 C7.1459869,0 6.50902508,0.360605034 6.15504167,0.949791667 L0.280041667,10.7414583 C0.0969176761,11.0460037 -1.23209662e-05,11.3946378 -1.23209662e-05,11.75 C-0.00758042603,16.0663731 3.48367543,19.5725301 7.80004167,19.5833333 L7.81570833,19.5833333 C9.75003686,19.5882688 11.6168794,18.8726691 13.0522917,17.5760417 C16.0171492,20.2556967 20.5292675,20.2556967 23.494125,17.5760417 C26.4604562,20.2616016 30.9794188,20.2616016 33.94575,17.5760417 C36.2421905,19.6477597 39.5441143,20.1708521 42.3684437,18.9103691 C45.1927731,17.649886 47.0084685,14.8428276 47.0000295,11.75 C47.0000295,11.3946378 46.9030823,11.0460037 46.7199583,10.7414583 Z">
                                                </path>
                                                <path class="color-background"
                                                    d="M39.198,22.4912623 C37.3776246,22.4928106 35.5817531,22.0149171 33.951625,21.0951667 L33.92225,21.1107282 C31.1430221,22.6838032 27.9255001,22.9318916 24.9844167,21.7998837 C24.4750389,21.605469 23.9777983,21.3722567 23.4960833,21.1018359 L23.4745417,21.1129513 C20.6961809,22.6871153 17.4786145,22.9344611 14.5386667,21.7998837 C14.029926,21.6054643 13.533337,21.3722507 13.0522917,21.1018359 C11.4250962,22.0190609 9.63246555,22.4947009 7.81570833,22.4912623 C7.16510551,22.4842162 6.51607673,22.4173045 5.875,22.2911849 L5.875,44.7220845 C5.875,45.9498589 6.7517757,46.9451667 7.83333333,46.9451667 L19.5833333,46.9451667 L19.5833333,33.6066734 L27.4166667,33.6066734 L27.4166667,46.9451667 L39.1666667,46.9451667 C40.2482243,46.9451667 41.125,45.9498589 41.125,44.7220845 L41.125,22.2822926 C40.4887822,22.4116582 39.8442868,22.4815492 39.198,22.4912623 Z">
                                                </path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </div>
                        <span class="nav-link-text ms-1">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  active" href="./pages/tables.html">
                        <div
                            class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <svg width="12px" height="12px" viewBox="0 0 42 42" version="1.1"
                                xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <title>office</title>
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g transform="translate(-1869.000000, -293.000000)" fill="#FFFFFF"
                                        fill-rule="nonzero">
                                        <g transform="translate(1716.000000, 291.000000)">
                                            <g id="office" transform="translate(153.000000, 2.000000)">
                                                <path class="color-background opacity-6"
                                                    d="M12.25,17.5 L8.75,17.5 L8.75,1.75 C8.75,0.78225 9.53225,0 10.5,0 L31.5,0 C32.46775,0 33.25,0.78225 33.25,1.75 L33.25,12.25 L29.75,12.25 L29.75,3.5 L12.25,3.5 L12.25,17.5 Z">
                                                </path>
                                                <path class="color-background"
                                                    d="M40.25,14 L24.5,14 C23.53225,14 22.75,14.78225 22.75,15.75 L22.75,38.5 L19.25,38.5 L19.25,22.75 C19.25,21.78225 18.46775,21 17.5,21 L1.75,21 C0.78225,21 0,21.78225 0,22.75 L0,40.25 C0,41.21775 0.78225,42 1.75,42 L40.25,42 C41.21775,42 42,41.21775 42,40.25 L42,15.75 C42,14.78225 41.21775,14 40.25,14 Z M12.25,36.75 L7,36.75 L7,33.25 L12.25,33.25 L12.25,36.75 Z M12.25,29.75 L7,29.75 L7,26.25 L12.25,26.25 L12.25,29.75 Z M35,36.75 L29.75,36.75 L29.75,33.25 L35,33.25 L35,36.75 Z M35,29.75 L29.75,29.75 L29.75,26.25 L35,26.25 L35,29.75 Z M35,22.75 L29.75,22.75 L29.75,19.25 L35,19.25 L35,22.75 Z">
                                                </path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </div>
                        <span class="nav-link-text ms-1">Tables</span>
                    </a>
                </li>
            </ul>
        </div>

    </aside>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur"
            navbar-scroll="true">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a>
                        </li>
                        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Tables</li>
                    </ol>
                    <h6 class="font-weight-bolder mb-0">Tables</h6>
                </nav>

            </div>
        </nav>
        <!-- End Navbar -->
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6>Foods table</h6>
                        </div>
                        <div class="card-body px-0 pt-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Id</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Title</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Description</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Price</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Price Id</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Time Value</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Time Id</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Star</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Location Id</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Category Id</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Image Path</th>
                                            <th class="text-secondary opacity-7"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="foodTableBody">
                                        <?php if (!empty($pagedFoodItems)): ?>
                                            <?php foreach ($pagedFoodItems as $id => $food): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($id) ?></td>
                                                    <td><?= htmlspecialchars($food['Title'] ?? '') ?></td>
                                                    <td class="description"><?= htmlspecialchars($food['Description'] ?? '') ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($food['Price'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($food['PriceId'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($food['TimeValue'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($food['TimeId'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($food['Star'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($food['LocationId'] ?? '') ?></td>
                                                    <td class="text-center"><?= htmlspecialchars($food['CategoryId'] ?? '') ?>
                                                    </td>
                                                    <td class="description"><?= htmlspecialchars($food['ImagePath'] ?? '') ?>
                                                    </td>
                                                    <td class="align-middle">
                                                        <a href="#" class="text-secondary font-weight-bold text-xs"
                                                            data-bs-toggle="modal" data-bs-target="#editFoodModal"
                                                            onclick="openEditModal('<?= htmlspecialchars($food['Id'] ?? '') ?>', '<?= htmlspecialchars($food['Title'] ?? '') ?>', '<?= htmlspecialchars($food['Description'] ?? '') ?>', '<?= htmlspecialchars($food['Price'] ?? '') ?>', '<?= htmlspecialchars($food['ImagePath'] ?? '') ?>')">
                                                            Edit
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="12" class="text-center">No data available</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>

                                <!-- Pagination -->
                                <div style="margin-top: 20px;">
                                    <?php if ($totalPages > 1): ?>
                                        <nav>
                                            <ul class="pagination">
                                                <?php if ($page > 1): ?>
                                                    <li class="page-item"><a class="page-link" href="?page=1">First</a></li>
                                                    <li class="page-item"><a class="page-link"
                                                            href="?page=<?= $page - 1 ?>">Previous</a></li>
                                                <?php endif; ?>

                                                <?php
                                                // Show up to 5 page numbers around the current page
                                                $startPage = max(1, $page - 2);
                                                $endPage = min($totalPages, $page + 2);

                                                for ($i = $startPage; $i <= $endPage; $i++): ?>
                                                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                                    </li>
                                                <?php endfor; ?>

                                                <?php if ($page < $totalPages): ?>
                                                    <li class="page-item"><a class="page-link"
                                                            href="?page=<?= $page + 1 ?>">Next</a></li>
                                                    <li class="page-item"><a class="page-link"
                                                            href="?page=<?= $totalPages ?>">Last</a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </nav>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Modal Edit Food -->
            <div class="modal fade" id="editFoodModal" tabindex="-1" aria-labelledby="editFoodModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editFoodModalLabel">Edit Food</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="editFoodForm">
                                <input type="hidden" id="foodId">

                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" class="form-control" id="foodTitle">
                                </div>

                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea class="form-control" id="foodDescription"></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Price</label>
                                    <input type="number" class="form-control" id="foodPrice">
                                </div>

                                <div class="form-group">
                                    <label>Image Path</label>
                                    <input type="text" class="form-control" id="foodImage">
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" onclick="deleteFood()">Delete</button>
                            <button type="button" class="btn btn-primary" onclick="updateFood()">Update</button>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6>Orders table</h6>
                        </div>
                        <div class="card-body px-0 pt-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center justify-content-center mb-0">
                                    <thead>
                                        <tr>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Order Id</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                User ID</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Subtotal ($)</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">
                                                Tax ($)</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">
                                                Delivery Fee ($)</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">
                                                Total Price ($)</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">
                                                Items</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $orderId => $order): ?>

                                            <tr>
                                                <td><?php echo $orderId; ?></td>
                                                <td><?php echo htmlspecialchars($order['userId'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($order['subtotal'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($order['tax'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($order['delivery_fee'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($order['total_price'] ?? ''); ?></td>
                                                <td>
                                                    <ul>
                                                        <?php foreach ($order['items'] as $itemId => $item): ?>
                                                            <li><?php echo $itemId; ?>: <?php echo json_encode($item); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </td>
                                                <td class="align-middle">
                                                    <a href="#" class="text-secondary font-weight-bold text-xs delete-order"
                                                        data-order-id="<?php echo htmlspecialchars($orderId); ?>">
                                                        Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="footer pt-3  ">
                <div class="container-fluid">

                </div>
            </footer>
        </div>
    </main>


    <!--   Core JS Files   -->
    <script src="./assets/js/core/popper.min.js"></script>
    <script src="./assets/js/core/bootstrap.min.js"></script>
    <script src="./assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="./assets/js/plugins/smooth-scrollbar.min.js"></script>


    <script>
        function openEditModal(id, title, description, price, image) {
            document.getElementById("foodId").value = id;
            document.getElementById("foodTitle").value = title;
            document.getElementById("foodDescription").value = description;
            document.getElementById("foodPrice").value = price;
            document.getElementById("foodImage").value = image;
        }

        function updateFood() {
            let foodId = document.getElementById("foodId").value;

            let updatedData = {
                Title: document.getElementById("foodTitle").value,
                Description: document.getElementById("foodDescription").value,
                Price: parseFloat(document.getElementById("foodPrice").value),
                ImagePath: document.getElementById("foodImage").value
            };
            console.log(foodId);
            console.log(updatedData);  // In ra để kiểm tra ID


            fetch(`update_food.php?id=${foodId}`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(updatedData)
            })
                .then(response => response.json())
                .then(data => {
                    alert("Updated successfully!");
                    location.reload();
                })
                .catch(error => console.error("Error:", error));
        }

        function deleteFood() {
            let foodId = document.getElementById("foodId").value;
            if (!confirm("Are you sure you want to delete this food?")) return;

            fetch(`delete_food.php?id=${foodId}`, { method: "POST" })
                .then(response => response.json())
                .then(data => {
                    alert("Deleted successfully!");
                    location.reload();
                })
                .catch(error => console.error("Error:", error));
        }


        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".delete-order").forEach(function (btn) {
                btn.addEventListener("click", function (event) {
                    event.preventDefault();
                    let orderId = this.getAttribute("data-order-id");

                    if (confirm("Bạn có chắc muốn xóa đơn hàng #" + orderId + " không?")) {
                        fetch("delete_order.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "orderId=" + encodeURIComponent(orderId)
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === "success") {
                                    alert(data.message);
                                    location.reload();  // Reload lại trang sau khi xóa
                                } else {
                                    alert("Lỗi: " + data.message);
                                }
                            })
                            .catch(error => console.error("Error:", error));
                    }
                });
            });
        });


    </script>





    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="./assets/js/soft-ui-dashboard.min.js?v=1.1.0"></script>
    <!-- Bootstrap JS (Cần jQuery cho Bootstrap 4) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>