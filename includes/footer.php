        </main>
    </div>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <h4>Musumba Steel Tanzania Limited</h4>
                <p><?= t('brand.short'); ?></p>
                <p class="footer-meta"><?= t('brand.tagline'); ?></p>
                <p class="footer-meta"><?= t('footer.hours'); ?></p>
            </div>
            <div>
                <h5><?= t('footer.menu'); ?></h5>
                <ul>
                    <li><a href="?page=residential-roofing"><?= t('nav.building_solutions'); ?></a></li>
                    <li><a href="?page=need-a-new-roof"><?= t('nav.resources'); ?></a></li>
                    <li><a href="?page=distributors"><?= t('nav.buy.distributors'); ?></a></li>
                    <li><a href="?page=about"><?= t('nav.who_we_are'); ?></a></li>
                    <li><a href="?page=photos"><?= t('nav.gallery'); ?></a></li>
                    <li><a href="?page=services"><?= t('nav.services'); ?></a></li>
                    <li><a href="?page=contact-us"><?= t('nav.contact'); ?></a></li>
                </ul>
            </div>
            <div>
                <h5><?= t('nav.where_to_buy'); ?></h5>
                <p><?= t('footer.factory_address'); ?></p>
                <p><a href="tel:+255781502260">+255 781 502 260</a></p>
                <p><a href="tel:+255761037271">+255 761 037 271</a></p>
                <p><a href="mailto:info@musumba-steel.com">info@musumba-steel.com</a></p>
                <p><a href="mailto:sales@musumba-steel.com">sales@musumba-steel.com</a></p>
                <p><a href="mailto:marketing@musumba-steel.com">marketing@musumba-steel.com</a></p>
            </div>
            <div>
                <h5><?= t('footer.follow'); ?></h5>
                <form class="newsletter-form" action="mailto:marketing@musumba-steel.com" method="get" enctype="text/plain">
                    <label class="sr-only" for="newsletterEmail"><?= t('footer.newsletter'); ?></label>
                    <input id="newsletterEmail" type="email" name="body" placeholder="<?= t('footer.newsletter'); ?>" required>
                    <button type="submit"><?= t('footer.submit'); ?></button>
                </form>
            </div>
        </div>
        <p class="copyright">
            &copy; <?= date('Y'); ?> Musumba Steel Tanzania Limited. <?= t('footer.rights'); ?>
        </p>
    </footer>

    <aside class="float-dock" aria-label="Quick contact">
        <a href="https://wa.me/255781502260" target="_blank" rel="noopener" title="WhatsApp">WA</a>
        <a href="tel:+255761037271" title="<?= t('float.phone'); ?>">☎</a>
        <a href="mailto:info@musumba-steel.com" title="Email">✉</a>
        <button type="button" id="backToTop" title="<?= t('float.top'); ?>">↑</button>
    </aside>

    <script src="assets/js/app.js?v=20260904q"></script>
</body>

</html>
