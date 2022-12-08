<?php include __DIR__ . '/../_partials/header.php'; ?>

    <div class="card">
        <div class="card-body">
            <a href="/installation/index">
                <button class="btn btn-warning my-2">Добавить аккаунт</button>
            </a>
            <h3>Список аккаунтов</h3>
            <?php if ($rows): ?>
                <ul class="list-group">
                    <?php foreach ($rows as $item): ?>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a class="btn"
                               href="/billing/info?phone=<?php echo $item->getValues()['billing_phone'][0]; ?>"><?php echo $item->getValues()['billing_phone'][0]; ?>
                                @whatsapp_<span class=""
                                                id="plan_rice"><?php echo $item->getValues()['billing_phone'][0]; ?></a>
                            <div>
                                <button type="button" class="btn" data-bs-toggle="modal"
                                        data-bs-target="#confirm-delete-modal"
                                        data-bs-account="<?php echo $item->getId(); ?>"
                                        data-bs-billing_phone="<?php echo $item->getValues()['billing_phone'][0]; ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </li>

                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <a href="/oauth/authorize" class="btn btn-warning mt-2">Переустановить приложение</a>
            <a href="#" class="btn btn-warning mt-2">Инструкция</a>
        </div>
    </div>

    <!--Modal-->
    <div class="modal fade" id="confirm-delete-modal" tabindex="-1" aria-labelledby="exampleModalLabel"
         style="display: none;" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Удаление аккаунта</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Вы действительно хотите удалить аккаунт?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <form id="account-delete-form">
                        <input id="account-input" type="hidden" name="rowId" value="">
                        <input id="billing-phone" type="hidden" name="billingPhone"
                               value="">
                        <button type="submit" class="btn btn-danger">Удалить аккаунт</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        let deleteModal = document.getElementById('confirm-delete-modal');
            deleteModal.addEventListener('show.bs.modal', function (event) {
                let button = event.relatedTarget
                let account = button.getAttribute('data-bs-account')
                let accountInput = deleteModal.querySelector('#account-input');
                accountInput.value = account

                let billingPhone = button.getAttribute('data-bs-billing_phone')
                let billingPhoneInput = deleteModal.querySelector('#billing-phone');
                billingPhoneInput.value = billingPhone
            })

        document.getElementById('account-delete-form').onsubmit = function deleteAccountSubmit(event) {
            let form = event.target;

            let elems = form.elements;

            let data = {
                billingPhone: elems.billingPhone.value,
                rowId: elems.rowId.value
            };

            fetch('/billing/index', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json;charset=utf-8'
                },
                body: JSON.stringify(data)
            }).then(() => {
                bootstrap.Modal.getInstance(deleteModal).hide();
                location.reload();
            })

            return false;
        }
    </script>

<?php include __DIR__ . '/../_partials/footer.php'; ?>