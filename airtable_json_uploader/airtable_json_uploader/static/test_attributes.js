document.addEventListener("DOMContentLoaded", () => {
  const elements = {
    selectBase: document.getElementById("select-base"),
    selectAttrTable: document.getElementById("select-attr-table"),
    jsonEditor: document.getElementById("json-payload-editor"),
    btnSyncNow: document.getElementById("btn-sync-attributes-now"),
    statusBox: document.getElementById("results-status-box"),
    statusMsg: document.getElementById("results-status-msg"),
    statAttrPairs: document.getElementById("stat-attr-pairs"),
    statRecordIds: document.getElementById("stat-record-ids"),
    attrPairsContainer: document.getElementById("attr-pairs-container"),
    recordIdsContainer: document.getElementById("record-ids-container")
  };

  init();

  async function init() {
    await loadBases();
    renderExtractedPairsPreview();

    elements.jsonEditor.addEventListener("input", renderExtractedPairsPreview);
    elements.btnSyncNow.addEventListener("click", executeSync);
  }

  async function loadBases() {
    try {
      const res = await fetch("/api/workspaces-and-bases");
      const data = await res.json();
      if (data.error) throw new Error(data.error);

      const bases = data.bases || [];
      elements.selectBase.innerHTML = '<option value="">-- Select Airtable Base --</option>';
      bases.forEach(b => {
        const opt = document.createElement("option");
        opt.value = b.id;
        opt.textContent = b.name;
        elements.selectBase.appendChild(opt);
      });

      if (bases.length > 0) {
        elements.selectBase.selectedIndex = 1; // Default to first available base
      }
    } catch (e) {
      alert("Failed to load bases: " + e.message);
    }
  }

  function parseCurrentPairs() {
    try {
      const jsonText = elements.jsonEditor.value;
      const data = JSON.parse(jsonText);
      const features = data.product_features || data.attributes || data;

      const pairs = [];
      if (typeof features === "object" && features !== null) {
        for (const [k, v] of Object.entries(features)) {
          if (Array.isArray(v)) {
            v.forEach(sub => pairs.push({ name: k, val: String(sub) }));
          } else {
            pairs.push({ name: k, val: String(v) });
          }
        }
      }
      return pairs;
    } catch (e) {
      return [];
    }
  }

  function renderExtractedPairsPreview() {
    const pairs = parseCurrentPairs();
    elements.statAttrPairs.textContent = pairs.length;
    elements.attrPairsContainer.innerHTML = "";

    if (pairs.length === 0) {
      elements.attrPairsContainer.innerHTML = '<div style="color:var(--muted); font-style:italic;">Invalid JSON syntax.</div>';
      return;
    }

    pairs.forEach(p => {
      const badge = document.createElement("span");
      badge.className = "attr-badge";
      badge.innerHTML = `<strong>${escapeHtml(p.name)}:</strong> ${escapeHtml(p.val)}`;
      elements.attrPairsContainer.appendChild(badge);
    });
  }

  async function executeSync() {
    const baseId = elements.selectBase.value;
    if (!baseId) return alert("Please select an Airtable Base.");

    const attrTable = elements.selectAttrTable.value || "Product attributes";

    let featuresInput = null;
    try {
      const data = JSON.parse(elements.jsonEditor.value);
      featuresInput = data.product_features || data.attributes || data;
    } catch (e) {
      return alert("Invalid JSON payload: " + e.message);
    }

    elements.btnSyncNow.disabled = true;
    elements.btnSyncNow.textContent = "Syncing to Airtable...";
    elements.statusBox.classList.remove("hidden");
    elements.statusMsg.textContent = `Syncing attribute pairs to table '${attrTable}'...`;

    try {
      const res = await fetch("/api/test-attributes-sync", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          base_id: baseId,
          attr_table_name: attrTable,
          product_features: featuresInput
        })
      });

      const data = await res.json();
      if (data.error) throw new Error(data.error);

      const recordIds = data.record_ids || [];
      elements.statRecordIds.textContent = recordIds.length;

      elements.statusMsg.textContent = `✓ SUCCESS! Synced ${recordIds.length} attribute record(s) in '${data.attr_table_name}'.`;

      elements.recordIdsContainer.innerHTML = recordIds.map((id, i) =>
        `<div style="padding:4px 0;">${i+1}. <code style="color:var(--accent);">${escapeHtml(id)}</code></div>`
      ).join("");

    } catch (e) {
      elements.statusMsg.textContent = `Error: ${e.message}`;
      alert("Attributes Sync Failed: " + e.message);
    } finally {
      elements.btnSyncNow.disabled = false;
      elements.btnSyncNow.textContent = "⚡ SYNC ATTRIBUTES NOW";
    }
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }
});
