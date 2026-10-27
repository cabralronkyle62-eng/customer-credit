    </main>

    <footer class="site-footer">
        <div class="container">
           
        </div>
    </footer>

    <div class="delete-modal" id="delete-confirmation" hidden>
        <button
            class="delete-modal-backdrop"
            type="button"
            aria-label="Close delete confirmation"
            data-delete-cancel
        ></button>

        <section
            class="delete-modal-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-modal-title"
            aria-describedby="delete-modal-description"
        >
            <div class="delete-modal-icon" aria-hidden="true">!</div>

            <div class="delete-modal-copy">
                <p class="delete-modal-label">Confirm deletion</p>
                <h2 id="delete-modal-title">Delete this customer?</h2>
                <p id="delete-modal-description">
                    This customer record will be permanently removed. This action cannot be undone.
                </p>
            </div>

            <div class="delete-modal-actions">
                <button class="button button-secondary" type="button" data-delete-cancel>
                    Cancel
                </button>
                <button class="button delete-confirm-button" type="button" data-delete-confirm>
                    Delete customer
                </button>
            </div>
        </section>
    </div>

    <script src="assets/app.js"></script>
</body>
</html>
