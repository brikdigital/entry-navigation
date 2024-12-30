// Direct port of the PHP code in EntryNavigation#_registerSidebarPanels

const panelHolder = $("#details-container .details");
const navPanel = $(".navigation-element-sidebar");

let revisionNotesField = null;
let statusFieldDisabled = false;
let revisionNotesDisabled = false;

const slugField = $("#slug-field");
const slugFieldParent = $(slugField.parent());
const statusFieldset = slugFieldParent.next().next();
if (statusFieldset.get(0).type !== "fieldset") statusFieldDisabled = true;

if (!statusFieldDisabled) {
    revisionNotesField = statusFieldset.next();
    if (!revisionNotesField.get(0).id.startsWith("textarea")) revisionNotesDisabled = true;
}

if (!revisionNotesDisabled && revisionNotesField) {
    navPanel.insertAfter(revisionNotesField);
} else if (!statusFieldDisabled && statusFieldset) {
    navPanel.insertAfter(statusFieldset);
} else {
    panelHolder.prepend(navPanel);
}