<div class="modal-header bg-primary text-white border-0">
    <h5 class="modal-title fw-bold" id="ajaxModalLabel"><i class="fas fa-edit me-2"></i>Edit SMS Template</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<form action="{{ route('sms_templates.update', $template->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="mb-3">
            <label for="template_name" class="form-label fw-bold">Template Name <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control" name="template_name" id="template_name"
                value="{{ str_replace('_', ' ', ucwords($template->template_name, '_')) }}" required>
        </div>

        <div class="mb-3">
            <label for="message_body" class="form-label fw-bold">Message Content <span
                    class="text-danger">*</span></label>
            <textarea class="form-control" name="message_body" id="message_body" rows="5" required>{{ $template->message_body }}</textarea>

            <div class="mt-3 p-3 bg-light border rounded">
                <small class="fw-bold d-block mb-2 text-primary"><i class="fas fa-tags me-1"></i> Click to insert
                    variables:</small>

                <span class="badge bg-secondary me-1 mb-2 insert-tag" style="cursor: pointer;"
                    data-tag="{customer_name}">Customer Name</span>
                <span class="badge bg-secondary me-1 mb-2 insert-tag" style="cursor: pointer;"
                    data-tag="{invoice_no}">Reference / Invoice</span>
                <span class="badge bg-secondary me-1 mb-2 insert-tag" style="cursor: pointer;"
                    data-tag="{quantity}">Quantity</span>
                <span class="badge bg-secondary me-1 mb-2 insert-tag" style="cursor: pointer;"
                    data-tag="{grand_total}">Total Amount</span>
                <span class="badge bg-secondary me-1 mb-2 insert-tag" style="cursor: pointer;"
                    data-tag="{paid}">Paid</span>
                <span class="badge bg-secondary me-1 mb-2 insert-tag" style="cursor: pointer;"
                    data-tag="{due_amount}">Due</span>
                <span class="badge bg-secondary me-1 mb-2 insert-tag" style="cursor: pointer;"
                    data-tag="{discount}">Discount</span>
                <span class="badge bg-secondary me-1 mb-2 insert-tag" style="cursor: pointer;"
                    data-tag="{tax}">Tax</span>
                <span class="badge bg-secondary me-1 mb-2 insert-tag" style="cursor: pointer;"
                    data-tag="{previous_due}">Previous Due</span>
                <span class="badge bg-secondary me-1 mb-2 insert-tag" style="cursor: pointer;"
                    data-tag="{invoice_link}">Invoice Link</span>
            </div>
        </div>

        <div class="sms-preview-box mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="fw-bold text-primary mb-0"><i class="fas fa-mobile-alt me-1"></i> Live Preview</small>
                <small class="text-muted" id="smsCharCount">0 chars</small>
            </div>
            <div class="sms-phone">
                <div class="sms-phone-notch"></div>
                <div class="sms-bubble" id="smsPreviewText">
                    <span class="text-muted">Start typing to see preview...</span>
                </div>
            </div>
            <small class="text-muted d-block mt-2">Preview uses sample data so you can see how the SMS will look.</small>
        </div>

        <div class="form-check form-switch mt-3">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                {{ $template->is_active ? 'checked' : '' }}>
            <label class="form-check-label fw-bold ms-2 {{ $template->is_active ? 'text-success' : 'text-muted' }}"
                for="is_active">Active Template</label>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Template</button>
    </div>
</form>

<script>
    (function() {
        var sampleData = {
            '{customer_name}': @json(__('John Doe')),
            '{invoice_no}': 'INV-2026-001',
            '{quantity}': '3',
            '{grand_total}': '1,500.00',
            '{paid}': '1,000.00',
            '{due_amount}': '500.00',
            '{discount}': '50.00',
            '{tax}': '75.00',
            '{previous_due}': '200.00',
            '{invoice_link}': 'https://example.com/inv/001'
        };

        function renderSmsPreview() {
            var raw = $('#message_body').val() || '';
            var preview = raw;
            Object.keys(sampleData).forEach(function(tag) {
                preview = preview.split(tag).join(sampleData[tag]);
            });
            $('#smsCharCount').text(raw.length + ' chars');
            if (!raw.trim()) {
                $('#smsPreviewText').html('<span class="text-muted">Start typing to see preview...</span>');
            } else {
                $('#smsPreviewText').text(preview);
            }
        }

        $(document).off('click', '.insert-tag').on('click', '.insert-tag', function() {
            var tag = $(this).data('tag');
            var textarea = $('#message_body');
            var cursorPos = textarea.prop('selectionStart');
            var v = textarea.val();
            var textBefore = v.substring(0, cursorPos);
            var textAfter = v.substring(cursorPos, v.length);

            textarea.val(textBefore + tag + textAfter);
            textarea.prop('selectionStart', cursorPos + tag.length);
            textarea.prop('selectionEnd', cursorPos + tag.length);
            textarea.focus();
            renderSmsPreview();
        });

        $('#message_body').off('input.smsPreview').on('input.smsPreview', renderSmsPreview);
        renderSmsPreview();
    })();
</script>
