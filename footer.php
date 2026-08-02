<footer class="footer" style="margin-top:auto;">
    <div class="footer-inner">
        <div class="footer-col">
            <div class="footer-brand">
                <img src="<?php echo BASE_URL; ?>/logo.png" alt="Logo" class="footer-logo" onerror="this.style.background='#4F8A30'">
                <span class="footer-brand-text">方块人快乐小窝</span>
            </div>
            <p>一个充满创造力、冒险精神与温暖的 Minecraft 社区。提供优质服务器供您游戏</p>
        </div>
        <div class="footer-col">
            <h4>快速链接</h4>
            <p><a href="<?php echo BASE_URL; ?>/modules/rules/index.php" style="color:inherit; text-decoration:none;">服务器规则</a></p>
            <p><a href="<?php echo BASE_URL; ?>/modules/announcements/index.php" style="color:inherit; text-decoration:none;">查看公告</a></p>
            <p><a href="<?php echo BASE_URL; ?>/modules/help/index.php" style="color:inherit; text-decoration:none;">常见问题</a></p>
            <p><a href="https://docs.qq.com/sheet/DWk5DandtVGdzQ1du" target="_blank" rel="noopener" style="color:inherit; text-decoration:none;">官方文档</a></p>
        </div>
        <div class="footer-col">
            <h4>社区平台</h4>
            <p><a href="<?php echo BASE_URL; ?>/modules/wechat/index.php" style="color:inherit; text-decoration:none;">微信小窝</a></p>
            <p><a href="https://qun.qq.com/universal-share/share?ac=1&authKey=%2BnvwR6Quqqh1Q9yaaraNvYXA8vKPmWDinTau8jGE50GlixWjv4erzCRbnIKPRDa6&busi_data=eyJncm91cENvZGUiOiIxMTAyNjQwODgyIiwidG9rZW4iOiJPRW9xb3Bac2lDVjhiZVVxT0dia0NlWXpjQWN1anVwV1liaG8zei9Tb0dMMzIrY09waDNZTC9ONHgvWXhTV0swIiwidWluIjoiMTkxOTUzMTI4NiJ9&data=EcqpOac_KLB9R-Mp5zbgez_XHtYpeKvL8PU0L3dfFVWT-uZqCqWfXQtuQbELumM5FAF5yN6TX2ph-6nvr-uhOg&svctype=4&tempid=h5_group_info" target="_blank" rel="noopener" style="color:inherit; text-decoration:none;">QQ 群：1102640882</a></p>
            <p><a href="https://b23.tv/uKwA4mB" target="_blank" rel="noopener" style="color:inherit; text-decoration:none;">Bilibili 频道</a></p>
            <p><a href="https://pd.qq.com/s/22e94kq8u" target="_blank" rel="noopener" style="color:inherit; text-decoration:none;">腾讯频道</a></p>
        </div>
        <div class="footer-col">
            <h4>关于我们</h4>
            <p><a href="<?php echo BASE_URL; ?>/modules/servers/index.php" style="color:inherit; text-decoration:none;">服务器简介</a></p>
            <p><a href="<?php echo BASE_URL; ?>/modules/about/index.php" style="color:inherit; text-decoration:none;">管理团队</a></p>
            <p><a href="<?php echo BASE_URL; ?>/modules/groups/index.php" style="color:inherit; text-decoration:none;">玩家团体</a></p>
            <p><a href="<?php echo BASE_URL; ?>/modules/sponsor/index.php" style="color:inherit; text-decoration:none;">赞助我们</a></p>
        </div>
        <div class="footer-col">
            <h4>联系我们</h4>
            <p><i class="fas fa-envelope"></i> <a href="mailto:pumpkin@ururc.org" style="color:inherit; text-decoration:none;">pumpkin@ururc.org</a></p>
            <p><i class="fas fa-phone"></i> <a href="<?php echo BASE_URL; ?>/modules/feedback/index.php" style="color:inherit; text-decoration:none;">玩家支持</a></p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2025 方块人快乐小窝 · 保留所有权利 · <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener" style="color:inherit; text-decoration:none;">粤ICP备2026058942号</a></p>
        <p class="disclaimer">Minecraft 是由瑞典 Mojang AB 工作室开发一款沙盒创造类游戏，在中国，由网易公司独家代理。我们与 Microsoft、Mojang AB、网易公司不相隶属，无任何关系。</p>
    </div>
</footer>

<script>
(function() {
    // 主题切换
    const body = document.body;
    const themeToggle = document.getElementById('themeToggle');
    const applyTheme = (dark) => {
        body.classList.toggle('dark', dark);
        if (themeToggle) {
            themeToggle.innerHTML = dark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        }
        localStorage.setItem('mcTheme', dark ? 'dark' : 'light');
    };
    applyTheme(localStorage.getItem('mcTheme') === 'dark');
    if (themeToggle) {
        themeToggle.addEventListener('click', () => applyTheme(!body.classList.contains('dark')));
    }

    // 导航栏滚动透明（仅首页）
    const isHomePage = <?php echo isset($isHomePage) && $isHomePage ? 'true' : 'false'; ?>;
    const navbar = document.getElementById('navbar');
    if (isHomePage && navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 80);
        }, { passive: true });
    }

    // 移动端菜单
    const menuToggle = document.getElementById('menuToggle');
    const navCenter = document.getElementById('navCenter');
    if (menuToggle && navCenter) {
        navCenter.classList.remove('active');
        menuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            navCenter.classList.toggle('active');
        });
        navCenter.querySelectorAll('a, .dropdown-menu a').forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 900) navCenter.classList.remove('active');
            });
        });
        document.addEventListener('click', (e) => {
            if (!menuToggle.contains(e.target) && !navCenter.contains(e.target)) {
                navCenter.classList.remove('active');
            }
        });
        window.addEventListener('resize', () => {
            if (window.innerWidth > 900) navCenter.classList.remove('active');
        });
    }

    // 轮播初始化（自动播放、手动切换、淡入动画）
    class Carousel {
        constructor(section) {
            this.section = section;
            this.track = section.querySelector('.carousel-track');
            this.slides = section.querySelectorAll('.carousel-slide');
            this.prevBtn = section.querySelector('.carousel-arrow.prev');
            this.nextBtn = section.querySelector('.carousel-arrow.next');
            this.indicators = section.querySelector('.carousel-indicators');
            this.total = this.slides.length;
            this.current = 0;
            this.direction = section.dataset.direction || 'left';
            this.init();
        }
        init() {
            if (!this.track || this.total === 0) return;
            this.indicators.innerHTML = '';
            for (let i = 0; i < this.total; i++) {
                const dot = document.createElement('span');
                dot.className = 'dot' + (i === 0 ? ' active' : '');
                dot.addEventListener('click', () => this.goTo(i));
                this.indicators.appendChild(dot);
            }
            if (this.prevBtn) this.prevBtn.addEventListener('click', () => this.prev());
            if (this.nextBtn) this.nextBtn.addEventListener('click', () => this.next());
            this.startAuto();
            this.section.addEventListener('mouseenter', () => clearInterval(this.interval));
            this.section.addEventListener('mouseleave', () => this.startAuto());
        }
        update() {
            this.track.style.transform = `translateX(-${this.current * 100}%)`;
            const dots = this.indicators.querySelectorAll('.dot');
            dots.forEach((d, i) => d.classList.toggle('active', i === this.current));
        }
        goTo(i) { this.current = i; this.update(); this.resetAuto(); }
        next() {
            if (this.direction === 'right') this.current = (this.current - 1 + this.total) % this.total;
            else this.current = (this.current + 1) % this.total;
            this.update(); this.resetAuto();
        }
        prev() {
            if (this.direction === 'right') this.current = (this.current + 1) % this.total;
            else this.current = (this.current - 1 + this.total) % this.total;
            this.update(); this.resetAuto();
        }
        startAuto() { this.interval = setInterval(() => this.next(), 5000); }
        resetAuto() { clearInterval(this.interval); this.startAuto(); }
    }

    // 启动所有轮播
    document.querySelectorAll('.carousel-section').forEach(section => {
        try { new Carousel(section); } catch (e) { console.error(e); }
    });

    // 滚动淡入动画
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('visible');
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.carousel-section').forEach(section => observer.observe(section));
})();
</script>
</body>
</html>