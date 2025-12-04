@php
    // Les variables $rubrique et $pupitres sont passées depuis show.blade.php
@endphp

<!-- Modal pour créer/modifier une section ou un dossier -->
<div x-show="showSectionModal || showDossierModal" 
     x-cloak
     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
     @click.self="showSectionModal = false; showDossierModal = false">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <span x-text="showDossierModal ? 'Créer un dossier' : (editingSection ? 'Modifier la section' : 'Créer une section')"></span>
        </h3>
        <form id="section-form" @submit.prevent="window.saveSection()">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                    <input type="text" name="nom" id="section-nom" required
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="section-description" rows="3"
                              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ordre</label>
                    <input type="number" name="order" id="section-order" value="0" min="0"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <input type="hidden" name="type" id="section-type" value="section">
                <input type="hidden" name="dossier_id" id="section-dossier-id" value="">
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" @click="showSectionModal = false; showDossierModal = false" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Annuler
                </button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:opacity-90">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal pour créer une partition -->
<div x-show="showPartitionModal" 
     x-cloak
     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto"
     @click.self="showPartitionModal = false">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 my-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ajouter une partition</h3>
        <form id="partition-form" @submit.prevent="window.savePartition()" enctype="multipart/form-data">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                    <input type="text" name="title" id="partition-title" required
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="partition-description" rows="3"
                              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pupitre</label>
                    <select name="pupitre_id" id="partition-pupitre"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Sélectionner un pupitre (optionnel)</option>
                        @foreach($pupitres as $pupitre)
                            <option value="{{ $pupitre->id }}" {{ $pupitre->is_default ? 'selected' : '' }}>
                                {{ $pupitre->nom }}@if($pupitre->is_default) (Par défaut)@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fichiers</label>
                    <input type="file" name="files[]" id="partition-files" multiple
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-sm text-gray-500">Vous pouvez sélectionner plusieurs fichiers (audio, PDF, images, etc.)</p>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" @click="showPartitionModal = false" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Annuler
                </button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:opacity-90">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Initialiser le type de section/dossier selon le modal ouvert
    document.addEventListener('alpine:init', () => {
        Alpine.effect(() => {
            if (window.showDossierModal) {
                document.getElementById('section-type').value = 'dossier';
            } else if (window.showSectionModal) {
                document.getElementById('section-type').value = 'section';
            }
        });
    });
</script>

