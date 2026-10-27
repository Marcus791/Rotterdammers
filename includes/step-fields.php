<fieldset class="step-card">
    <legend class="step-number">Stap <?= $number ?></legend>
    <button class="step-remove" type="button">Verwijderen</button>

    <label>Titel
        <input name="stap_titel[]" value="<?= e($step['titel'] ?? '') ?>" placeholder="Bijvoorbeeld: Alles voorbereiden">
    </label>

    <label>Uitleg
        <textarea name="stap_tekst[]" rows="3" placeholder="Wat moet je in deze stap doen?"><?= e($step['tekst'] ?? '') ?></textarea>
    </label>

    <label>Foto URL (optioneel)
        <input name="stap_foto[]" type="url" value="<?= e($step['foto'] ?? '') ?>" placeholder="https://...">
    </label>
</fieldset>
