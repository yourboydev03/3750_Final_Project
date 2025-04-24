$(function(){
    const $input   = $('#user-search');
    const $results = $('#search-results');
    
    $input.on('input', function(){
        const q = $(this).val().trim();
        if (q.length < 2) {
            $results.empty();
            return;
        }
    
        $.getJSON('/Assignments/Final_Project/search_users.php', { q }, function(data){
            $results.empty();
            data.forEach(u => {
                $('<li>')
                    .text(u.display_name)
                    .data('id', u.id)
                    .appendTo($results);
            });
        });
    });
    
    $results.on('click', 'li', function(){
        window.location.href = 'view_profile.php?user_id=' + $(this).data('id');
    });
    
    $(document).click(e => {
        if (!$(e.target).closest('.search-li').length) {
            $results.empty();
        }
    });
});








