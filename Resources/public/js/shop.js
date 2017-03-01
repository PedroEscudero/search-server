$(document).ready(function(){
    var currentRequest = null;
    $('#search-field')
        .on('keyup', function() {
            var $this = $(this);
            currentRequest = $.ajax({
                type: 'GET',
                data: 'q=' + $this.val(),
                url: $this.data('action-url'),
                beforeSend: function() {
                    if (currentRequest != null) {
                        currentRequest.abort();
                    }
                },
                success: function(data) {
                    $('#results-container').html(data);
                    history.pushState('data', '', $this.data('main-action-url') + '?q=' + $this.val());
                }
            })
        });
});