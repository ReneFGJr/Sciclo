<?php
echo view('layout/header');
echo view('layout/navbar');
?>

<!-- Hero Section -->
<?php echo view('welcome_brand'); ?>

<?php echo view('seals/brand_seals', ['seals' => $seals]); ?>

<?php echo view('seals/seal_avaliation'); ?>

<?php echo view('layout/footer'); ?>