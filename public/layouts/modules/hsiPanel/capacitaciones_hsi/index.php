<script>
    document.addEventListener('DOMContentLoaded', function() {
        var header = document.querySelector('header');
        if (header) header.style.display = 'none';

        var headerLeft = document.querySelector('.headerLeft');
        if (headerLeft) headerLeft.style.display = 'none';

        var floatingButton = document.querySelector('.floating-button');
        if (floatingButton) floatingButton.style.display = 'none';
    });
</script>

<style>
    article {
        height: 100%;
    }
    .content {
        height: 100%;
    }
</style>