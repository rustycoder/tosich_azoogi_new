<dialog class="dash-enquiry-dialog" data-enquiry-dialog aria-labelledby="dash-enquiry-dialog-title">
    <div class="dash-enquiry-dialog-panel">
        <header class="dash-enquiry-dialog-head">
            <div class="dash-enquiry-dialog-heading">
                <h2 id="dash-enquiry-dialog-title" data-enquiry-dialog-title></h2>
                <div class="dash-enquiry-dialog-meta" data-enquiry-dialog-meta></div>
            </div>
            <button type="button" class="dash-drawer-close" data-enquiry-dialog-close aria-label="Close">×</button>
        </header>
        <div class="dash-enquiry-dialog-body" data-enquiry-dialog-body></div>
        <footer class="dash-enquiry-dialog-foot">
            <label class="dash-enquiry-move">
                <span>Move to</span>
                <select data-enquiry-move>
                    @foreach (\App\Enums\EnquiryStatus::cases() as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </label>
            <button type="button" class="dash-enquiry-delete" data-enquiry-delete>Delete</button>
        </footer>
    </div>
</dialog>
