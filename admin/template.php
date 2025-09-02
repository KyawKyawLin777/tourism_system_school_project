<?php
$page_title = "Page Title";
include_once 'includes/header.php';

// Your page-specific code here
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-plus me-1"></i>Add New
            </button>
        </div>
    </div>
</div>

<!-- Your page content here -->
<div class="card">
    <div class="card-body">
        Content goes here...
    </div>
</div>

<?php
// Include the footer with booking notifications
include_once 'includes/footer.php';
?>

<script>
// Your page-specific JavaScript here
</script>
</body>
</html>
