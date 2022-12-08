<?php include __DIR__.'/../_partials/header.php'; ?>

<div class="card">
    <div class="card-body">
        <?php if($_SESSION['billingPhone'] === true): ?>
            <div class="alert alert-danger" role="alert">
                This account already exists
            </div>
        <?php endif;?>

        <?php if($_SESSION['unauthorized'] === true): ?>
            <div class="alert alert-danger" role="alert">
                Incorrect authorization data in WhatsApp
            </div>
        <?php endif;?>

        <h3> Adding an account </h3>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="app">APP ID</label>
                <input type="text" class="form-control" id="app" name="app" placeholder="APP ID">
            </div>
            <div class="mb-3">
                <label for="app">APP Secret</label>
                <input type="password" class="form-control" id="app" name="secret" placeholder="APP Secret"
                       required>
            </div>
            <div class="mb-3">
                <label for="email">Email address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required readonly value="<?php echo $channelAccount['deliveryIdentifier']['value']?>">
            </div>
            <div class="mb-3">
                <button type="submit" class="btn btn-warning">Save</button>
                <a href="/billing/index"><button type="button" class="btn btn-warning">Personal cabinet</button></a>
            </div>
            <input type="hidden" name="install" value="1">
            <input type="hidden" name="webhook" value="<?php echo $_ENV['WEBHOOK'] ;?>">

        </form>
    </div>
</div>

<?php include __DIR__.'/../_partials/footer.php'; ?>
