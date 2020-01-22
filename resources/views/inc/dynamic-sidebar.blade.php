<script>
      /** add active class and stay opened when selected */
      var url = window.location.href;
      var urlParts = url.split('/');

      // for sidebar menu entirely but not cover treeview
      $('ul.nav-sidebar a').filter(function() {
            return this.href == url;
      }).addClass('active');
      
      // for treeview
      $('ul.nav-treeview a').filter(function() {
            return this.href == url;
      }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>