<?php include __DIR__ . '/../_partials/header.php'; ?>

    <div class="card">
        <div class="card-body">
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Текущий тариф:
                    <span class="badge bg-primary rounded-pill">
                            <?php echo $billingInfo['plan_price']; ?>
                        </span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Текущее кол-во клиентов:
                    <span class="badge bg-primary rounded-pill">
                            <?php echo $billingInfo['clients_active']; ?>
                        </span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Баланс
                    <span class="badge bg-primary rounded-pill">
                            <?php echo $billingInfo['balance']; ?>
                        </span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Оплаченный тариф для тарифицируемых вложений:
                    <span class="badge bg-primary rounded-pill">
                            <?php echo $billingInfo['files']; ?>
                        </span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Тариф действует до:
                    <span class="badge bg-primary rounded-pill">
                            <?php echo $billingInfo['files']; ?>
                        </span>
                </li>
            </ul>
            <a id="back-btn" href="/billing/index" class="btn btn-warning mt-2">Назад</a>
        </div>
    </div>

<?php include __DIR__ . '/../_partials/footer.php'; ?>