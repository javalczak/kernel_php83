        </div>
    </div>
</div>
<script>
(function () {
    var body = document.body;
    var toggle = document.getElementById('sidebar-toggle');

    if (document.cookie.indexOf('admin_sidebar=collapsed') !== -1) {
        body.classList.add('sidebar-collapsed');
    }

    toggle.addEventListener('click', function () {
        body.classList.toggle('sidebar-collapsed');
        var state = body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded';
        document.cookie = 'admin_sidebar=' + state + ';path=/;max-age=31536000';
    });
})();
</script>
</body>
</html>
