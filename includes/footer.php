    </main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <h4>Musumba Steel</h4>
                <p><?= t('brand.tagline'); ?></p>
            </div>
            <div>
                <h5><?= t('footer.quick_links'); ?></h5>
                <ul>
                    <li><a href="?page=welcome"><?= t('nav.welcome'); ?></a></li>
                    <li><a href="?page=our-projects"><?= t('nav.projects'); ?></a></li>
                    <li><a href="?page=contact-us"><?= t('nav.contact'); ?></a></li>
                </ul>
            </div>
            <div>
                <h5><?= t('footer.contact'); ?></h5>
                <p>Email: musumbasteeltanzanialimited@gmail.com</p>
                <p>Plot 71, shinyanga,Kahama,Mwendakulima</p>
            </div>
        </div>
        <p class="copyright">
            &copy; <?= date('Y'); ?> Musumba Steel. <?= t('footer.rights'); ?>
        </p>
    </footer>

    <script src="assets/js/app.js"></script>
</body>

</html>

