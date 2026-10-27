<?php
$editing = !empty($values['id']);
// Begin altijd met minstens één (lege) stap.
$formSteps = ($values['stappen'] ?? []) ?: [[]];
?>
<form method="post" class="editor">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int) ($values['id'] ?? 0) ?>">
    <h2><?= e($editing ? 'Recept aanpassen' : $formTitle) ?></h2>

    <label for="naam">Naam</label>
    <input id="naam" name="naam" value="<?= e($values['naam'] ?? '') ?>" required>

    <label for="ingredienten">Ingrediënten</label>
    <input id="ingredienten" name="ingredienten" value="<?= e($values['ingredienten'] ?? '') ?>" placeholder="Bijvoorbeeld pasta, kip, paprika" required>

    <div class="form-two">
        <div>
            <label for="thema_id">Categorie</label>
            <select id="thema_id" name="thema_id">
                <?php foreach (all_themes() as $theme): ?>
                    <option value="<?= $theme['id'] ?>" <?= (int) ($values['thema_id'] ?? 0) === (int) $theme['id'] ? 'selected' : '' ?>><?= e($theme['naam']) ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <div>
            <label for="tijd">Tijd in minuten</label>
            <input id="tijd" name="tijd" type="number" min="1" value="<?= e($values['tijd'] ?? '') ?>" placeholder="minuten" required>
        </div>
    </div>

    <label for="prijs">Prijs per persoon</label>
    <input id="prijs" name="prijs" type="number" min="0" step=".01" value="<?= e($values['prijs'] ?? '') ?>" placeholder="9.50" required>

    <label for="foto">Foto URL</label>
    <input id="foto" name="foto" type="url" value="<?= e($values['foto'] ?? '') ?>" placeholder="https://...">

    <h3 class="steps-title">Stappen</h3>
    <p class="form-hint">Voeg zoveel stappen toe als je nodig hebt. Een foto bij een stap is optioneel.</p>
    <div id="step-list">
        <?php foreach ($formSteps as $i => $step): ?>
            <?php $number = $i + 1; require __DIR__ . '/step-fields.php'; ?>
        <?php endforeach ?>
    </div>
    <template id="step-template">
        <?php $number = 1; $step = []; require __DIR__ . '/step-fields.php'; ?>
    </template>
    <button class="add-step" id="add-step" type="button">+ Stap toevoegen</button>

    <p class="form-message"><?= e($error) ?></p>
    <button type="submit"><?= e($editing ? 'Wijzigingen opslaan' : $buttonText) ?></button>
    <?php if ($editing): ?>
        <a class="text-link" href="admin.php">Annuleren</a>
    <?php endif ?>
</form>
<script>
    (() => {
        const list = document.getElementById('step-list');
        const template = document.getElementById('step-template');

        function renumber() {
            list.querySelectorAll('.step-number').forEach((legend, i) => legend.textContent = 'Stap ' + (i + 1));
        }

        document.getElementById('add-step').addEventListener('click', () => {
            list.append(template.content.cloneNode(true));
            renumber();
            list.lastElementChild.querySelector('input').focus();
        });

        list.addEventListener('click', event => {
            if (event.target.closest('.step-remove')) {
                event.target.closest('.step-card').remove();
                renumber();
            }
        });
    })();
</script>
