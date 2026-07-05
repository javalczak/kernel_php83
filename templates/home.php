<?php
declare(strict_types=1);
/** @var \Engine\Translation\Translator $translator */
$pageTitle = $translator->trans('home.title') . ' — ' . $translator->trans('home.tagline');
require __DIR__ . '/site/_header_home.php';
?>

<!-- Hero — recreated 1:1 from the old project's homepage_searchbox.scss -->
<style>
    .home-search-box {
        margin-top: 30px;
        max-width: 750px;
        width: 100%;
        text-align: center;
        background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAQAAAAECAYAAACp8Z5+AAAAHklEQVQYV2NkQAVnGJH4Z4BsE5gAmAOSBAnAOSABAHXuA9EOyvMzAAAAAElFTkSuQmCC) repeat;
        border: 1px solid #3f3f3f;
        padding: 25px;
        border-radius: 3px;
        box-sizing: border-box;
    }
    .home-search-box .header {
        color: white;
        font-size: 42px;
        line-height: 1.15;
        font-family: 'Quicksand', sans-serif;
        text-align: left;
    }
    .home-search-box .subHeader {
        color: white;
        font-size: 20px;
        margin: 8px 0 24px;
        font-family: 'Quicksand', sans-serif;
        text-align: left;
    }
    .home-search-field-frame {
        display: flex;
        width: 100%;
        position: relative;
    }
    #home-search-suggestions {
        display: none;
        position: absolute;
        top: 56px;
        left: 0;
        width: calc(100% - 130px);
        max-height: 280px;
        overflow: auto;
        box-sizing: border-box;
        padding: 8px;
        border: 1px solid #b1b1b1;
        border-radius: 3px;
        background-color: white;
        text-align: left;
        z-index: 30;
    }
    .home-suggestion-info {
        display: block;
        font-family: 'Quicksand', sans-serif;
        font-size: 11px;
        color: #5e5e5e;
        padding: 6px 20px;
        box-sizing: border-box;
        text-align: center;
    }
    .home-suggestion-info b { color: #ff7200; }
    .home-suggestion-header {
        display: block;
        margin: 0 0 4px 0;
        padding: 2px;
        font-family: 'Quicksand', sans-serif;
        font-size: 13px;
        font-weight: normal;
        color: #272727;
        border-bottom: 1px dotted #d9d9d9;
    }
    .home-suggestion {
        display: block;
        padding: 6px 10px;
        border-radius: 3px;
        font-family: 'Quicksand', sans-serif;
        font-size: 15px;
        color: #424242;
        cursor: pointer;
    }
    .home-suggestion span {
        font-size: 13px;
        color: #a4a4a4;
        margin-left: 6px;
    }
    .home-suggestion:hover, .home-suggestion.is-active { background-color: #fd8635; color: white; }
    .home-suggestion:hover span, .home-suggestion.is-active span { color: white; }
    .home-search-field {
        flex: 1;
        min-width: 0;
        color: #5e5e5e;
        font-family: Verdana, sans-serif;
        font-size: 16px;
        background-image: url(/assets/img/search-pin.png), linear-gradient(180deg, #fff 0%, #fff 0%, #dedede 100%);
        background-repeat: no-repeat, repeat;
        background-position: 15px center, center;
        height: 50px;
        padding: 0;
        border: 0;
        border-radius: 4px;
        text-indent: 45px;
        box-shadow: inset 0 0 1px rgba(0, 0, 0, 0.5);
        box-sizing: border-box;
    }
    .home-search-field:focus,
    .home-search-field:focus-visible {
        outline: none;
        box-shadow: inset 0 0 1px rgba(0, 0, 0, 0.5);
    }
    .home-search-field::placeholder { color: #c4c4c4; }
    .home-search-submit {
        flex-shrink: 0;
        background-color: #ff8624;
        color: white;
        width: 120px;
        height: 50px;
        font-family: Verdana, sans-serif;
        font-size: 16px;
        border: 0;
        border-radius: 4px;
        box-shadow: inset 0 0 1px rgba(0, 0, 0, 0.5);
        margin-left: 10px;
        cursor: pointer;
    }
    .home-search-submit:hover { background-color: #fd7322; }
</style>
<section class="relative min-h-[640px] flex items-center justify-center px-margin-mobile bg-cover"
          style="background-image: url('<?= htmlspecialchars($heroBg) ?>'); background-position: bottom left;">
    <div class="home-search-box">
        <div class="header"><?= htmlspecialchars($translator->trans('home.hero.headline')) ?></div>
        <div class="subHeader"><?= $translator->trans('home.hero.subheadline') ?></div>
        <div class="home-search-field-frame">
            <input type="text" id="home-search-field" class="home-search-field" autocomplete="off"
                   placeholder="<?= htmlspecialchars($translator->trans('home.hero.search_placeholder')) ?>">
            <div id="home-search-suggestions">
                <div class="home-suggestion-info">
                    Our directory is growing. New locations are being added gradually.<br>
                    Currently, we offer destinations in <b>Cyprus</b> and <b>Greece</b>.
                </div>
                <div id="home-search-results"></div>
            </div>
            <button type="button" id="home-search-submit" class="home-search-submit">
                <?= htmlspecialchars($translator->trans('home.hero.cta')) ?>
            </button>
        </div>
    </div>
</section>

<script>
(function () {
    var input       = document.getElementById('home-search-field');
    var suggestions = document.getElementById('home-search-suggestions');
    var results     = document.getElementById('home-search-results');
    var submitBtn    = document.getElementById('home-search-submit');
    var chosenUrl    = null;
    var debounceTimer = null;
    var activeIndex  = -1;

    function hideSuggestions() {
        suggestions.style.display = 'none';
        results.innerHTML = '';
        activeIndex = -1;
    }

    function getRows() {
        return Array.prototype.slice.call(results.querySelectorAll('.home-suggestion'));
    }

    function setActive(index) {
        var rows = getRows();
        if (!rows.length) return;
        index = (index + rows.length) % rows.length;
        rows.forEach(function (row) { row.classList.remove('is-active'); });
        rows[index].classList.add('is-active');
        rows[index].scrollIntoView({ block: 'nearest' });
        activeIndex = index;
    }

    function selectResult(result) {
        input.value = result.descriptor ? result.name + ' - ' + result.descriptor : result.name;
        chosenUrl = result.url;
        hideSuggestions();
        setTimeout(function () {
            window.location.href = result.url;
        }, 500);
    }

    // The info box at the top of the dropdown stays put — only the area
    // headers + matched rows below it get rebuilt on every keystroke.
    function renderSuggestions(matches) {
        results.innerHTML = '';
        activeIndex = -1;
        var lastArea = undefined;
        matches.forEach(function (result) {
            if (result.area !== lastArea) {
                lastArea = result.area;
                if (result.area) {
                    var header = document.createElement('div');
                    header.className = 'home-suggestion-header';
                    header.textContent = result.area;
                    results.appendChild(header);
                }
            }
            var row = document.createElement('div');
            row.className = 'home-suggestion';
            row.textContent = result.name;
            if (result.descriptor) {
                var span = document.createElement('span');
                span.textContent = '- ' + result.descriptor;
                row.appendChild(span);
            }
            row.addEventListener('mouseenter', function () {
                setActive(getRows().indexOf(row));
            });
            row.addEventListener('click', function () {
                selectResult(result);
            });
            row.dataset.result = JSON.stringify(result);
            results.appendChild(row);
        });
    }

    input.addEventListener('input', function () {
        chosenUrl = null;
        var query = input.value.trim();
        clearTimeout(debounceTimer);

        if (query.length < 2) {
            hideSuggestions();
            return;
        }

        suggestions.style.display = 'block';

        debounceTimer = setTimeout(function () {
            fetch('/location-suggest?q=' + encodeURIComponent(query))
                .then(function (r) { return r.json(); })
                .then(function (data) { renderSuggestions(data.results || []); });
        }, 200);
    });

    input.addEventListener('keydown', function (e) {
        var rows = getRows();
        if (!rows.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setActive(activeIndex + 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setActive(activeIndex - 1);
        } else if (e.key === 'Enter') {
            if (activeIndex !== -1) {
                e.preventDefault();
                selectResult(JSON.parse(rows[activeIndex].dataset.result));
            }
        } else if (e.key === 'Escape') {
            hideSuggestions();
        }
    });

    document.addEventListener('click', function (e) {
        if (e.target !== input) hideSuggestions();
    });

    submitBtn.addEventListener('click', function () {
        window.location.href = chosenUrl || '/search';
    });
})();
</script>

<!-- Featured stays -->
<section id="stays" class="max-w-[1440px] mx-auto px-margin-mobile md:px-margin-desktop py-xl">
    <h2 class="font-headline text-3xl font-bold text-on-surface mb-lg"><?= htmlspecialchars($translator->trans('home.stays.title')) ?></h2>

    <?php if (empty($stays)): ?>
    <div class="bg-white rounded-xl p-lg border border-outline-variant/20 text-center text-on-surface-variant warm-shadow">
        <?= htmlspecialchars($translator->trans('home.stays.empty')) ?>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-md">
        <?php foreach ($stays as $stay): ?>
        <?php $cover = $covers[(int)$stay['id']] ?? null; ?>
        <a href="/listing/<?= htmlspecialchars($stay['slug']) ?>" class="block bg-white rounded-xl overflow-hidden border border-outline-variant/10 warm-shadow hover:-translate-y-1 transition-transform">
            <?php if ($cover): ?>
            <img src="<?= htmlspecialchars($cover) ?>" alt="<?= htmlspecialchars($stay['title']) ?>" class="w-full h-48 object-cover">
            <?php else: ?>
            <div class="w-full h-48 bg-surface-container-low flex items-center justify-center text-outline text-sm">No photo yet</div>
            <?php endif; ?>
            <div class="p-md">
                <h3 class="font-headline text-lg font-bold text-on-surface"><?= htmlspecialchars($stay['title']) ?></h3>
                <p class="text-on-surface-variant text-sm mt-1">
                    <?= htmlspecialchars($stay['type_name']) ?>
                    <?php $loc = array_filter([$stay['city'], $stay['country']]); if (!empty($loc)): ?>
                    &middot; <?= htmlspecialchars(implode(', ', $loc)) ?>
                    <?php endif; ?>
                </p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<!-- How it works -->
<section id="how-it-works" class="bg-surface-container-low py-xl">
    <div class="max-w-[1440px] mx-auto px-margin-mobile md:px-margin-desktop">
        <h2 class="font-headline text-3xl font-bold text-on-surface mb-lg text-center"><?= htmlspecialchars($translator->trans('home.how.title')) ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
            <div class="bg-white rounded-xl p-lg warm-shadow">
                <h3 class="font-headline text-xl font-bold text-primary mb-2"><?= htmlspecialchars($translator->trans('home.how.guests.title')) ?></h3>
                <p class="text-on-surface-variant"><?= htmlspecialchars($translator->trans('home.how.guests.body')) ?></p>
            </div>
            <div class="bg-white rounded-xl p-lg warm-shadow">
                <h3 class="font-headline text-xl font-bold text-primary mb-2"><?= htmlspecialchars($translator->trans('home.how.hosts.title')) ?></h3>
                <p class="text-on-surface-variant"><?= htmlspecialchars($translator->trans('home.how.hosts.body')) ?></p>
            </div>
            <div class="bg-white rounded-xl p-lg warm-shadow">
                <h3 class="font-headline text-xl font-bold text-primary mb-2"><?= htmlspecialchars($translator->trans('home.how.simple.title')) ?></h3>
                <p class="text-on-surface-variant"><?= htmlspecialchars($translator->trans('home.how.simple.body')) ?></p>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/site/_footer.php'; ?>
