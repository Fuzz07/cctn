@once
<style>
    .delete-booking-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 100001;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .delete-booking-modal.is-open { display: flex; }
    .delete-booking-modal__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.62);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
    }
    .delete-booking-modal__dialog {
        position: relative;
        width: min(100%, 430px);
        max-height: calc(100vh - 2rem);
        overflow-y: auto;
        padding: 1.5rem;
        border: 1px solid var(--border-light);
        border-radius: 8px;
        background: var(--bg-card);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.3);
        text-align: center;
    }
    .delete-booking-modal__icon {
        display: flex;
        width: 48px;
        height: 48px;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        border-radius: 50%;
        background: #fef2f2;
        color: #dc2626;
    }
    .delete-booking-modal__title {
        margin: 0 0 0.5rem;
        color: var(--text-dark);
        font-size: 1.1rem;
        font-weight: 800;
    }
    .delete-booking-modal__message {
        margin: 0 0 1.25rem;
        color: var(--text-muted);
        font-size: 0.9rem;
        line-height: 1.5;
    }
    .delete-booking-modal__actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }
    .delete-booking-modal__button {
        min-height: 42px;
        padding: 0.65rem 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
    }
    .delete-booking-modal__cancel {
        border: 1px solid var(--border-light);
        background: var(--bg-page);
        color: var(--text-body);
    }
    .delete-booking-modal__confirm {
        border: 1px solid #dc2626;
        background: #dc2626;
        color: #fff;
    }
</style>

<div id="delete-booking-modal" class="delete-booking-modal" role="dialog" aria-modal="true" aria-labelledby="delete-booking-title" aria-describedby="delete-booking-message" aria-hidden="true">
    <div class="delete-booking-modal__backdrop" onclick="hideBookingDeleteModal()"></div>
    <div class="delete-booking-modal__dialog">
        <div class="delete-booking-modal__icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
        </div>
        <h2 id="delete-booking-title" class="delete-booking-modal__title">Delete booking?</h2>
        <p id="delete-booking-message" class="delete-booking-modal__message">This action cannot be undone.</p>
        <div class="delete-booking-modal__actions">
            <button type="button" class="delete-booking-modal__button delete-booking-modal__cancel" onclick="hideBookingDeleteModal()">Cancel</button>
            <button type="button" id="delete-booking-confirm" class="delete-booking-modal__button delete-booking-modal__confirm" onclick="confirmBookingDelete()">Delete booking</button>
        </div>
    </div>
</div>

<script>
    var deleteBookingForm = null;
    var deleteBookingTrigger = null;

    function showBookingDeleteModal(form) {
        if (!form) return;

        deleteBookingForm = form;
        deleteBookingTrigger = document.activeElement;

        var appointmentReference = form.dataset.bookingReference || 'selected booking';
        var message = 'Booking ' + appointmentReference + ' will be permanently deleted. This action cannot be undone.';
        document.getElementById('delete-booking-message').textContent = message;

        var modal = document.getElementById('delete-booking-modal');
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        document.getElementById('delete-booking-confirm').focus();

        return false;
    }

    function hideBookingDeleteModal() {
        var modal = document.getElementById('delete-booking-modal');
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';

        if (deleteBookingTrigger) deleteBookingTrigger.focus();
        var confirmButton = document.getElementById('delete-booking-confirm');
        confirmButton.disabled = false;
        confirmButton.textContent = 'Delete booking';
        deleteBookingForm = null;
        deleteBookingTrigger = null;
    }

    function confirmBookingDelete() {
        if (deleteBookingForm) {
            var confirmButton = document.getElementById('delete-booking-confirm');
            confirmButton.disabled = true;
            confirmButton.textContent = 'Deleting...';
            deleteBookingForm.submit();
        }
    }

    document.addEventListener('keydown', function (event) {
        var modal = document.getElementById('delete-booking-modal');
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            hideBookingDeleteModal();
        }
    });
</script>
@endonce
