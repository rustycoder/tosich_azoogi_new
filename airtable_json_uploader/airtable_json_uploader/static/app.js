document.addEventListener("DOMContentLoaded", () => {
  // Global State
  const state = {
    currentStep: 1,
    bases: [],
    workspaces: [],
    baseToWsp: {},
    selectedWorkspace: "all",
    selectedBaseId: "",
    selectedTableName: "",
    selectedAttrTableName: "",
    tableColumns: [],
    jsonPayload: null, // { items, keys, sample }
    activeMapping: {}, // { json_key: airtable_col_name }
    matchPreview: null,
    savedMappings: []
  };

  // DOM Elements
  const elements = {
    keyStatusPill: document.getElementById("key-status-pill"),
    keyStatusText: document.getElementById("key-status-text"),
    btnSettings: document.getElementById("btn-settings"),
    modalSettings: document.getElementById("modal-settings"),
    inputPatToken: document.getElementById("input-pat-token"),
    btnSaveToken: document.getElementById("btn-save-token"),

    selectWorkspace: document.getElementById("select-workspace"),
    selectBase: document.getElementById("select-base"),
    selectTable: document.getElementById("select-table"),
    selectAttrTable: document.getElementById("select-attr-table"),
    schemaPreview: document.getElementById("table-schema-preview"),

    colCount: document.getElementById("col-count"),
    tableColsPills: document.getElementById("table-cols-pills"),
    btnGotoStep2: document.getElementById("btn-goto-step2"),

    dropzone: document.getElementById("dropzone"),
    fileInput: document.getElementById("file-input"),
    jsonFilesChecklist: document.getElementById("json-files-checklist"),
    chkSelectAllJson: document.getElementById("chk-select-all-json"),
    jsonFilesSelectedTag: document.getElementById("json-files-selected-tag"),
    btnLoadFolderJson: document.getElementById("btn-load-folder-json"),
    jsonParsedSummary: document.getElementById("json-parsed-summary"),

    jsonItemsCount: document.getElementById("json-items-count"),
    jsonKeysCount: document.getElementById("json-keys-count"),
    jsonPreviewCode: document.getElementById("json-preview-code"),

    btnToggleViewTable: document.getElementById("btn-toggle-view-table"),
    btnToggleViewJson: document.getElementById("btn-toggle-view-json"),
    previewTableBox: document.getElementById("preview-table-box"),
    previewJsonBox: document.getElementById("preview-json-box"),
    previewTheadRow: document.getElementById("preview-thead-row"),
    previewTbody: document.getElementById("preview-tbody"),
    btnGotoStep3: document.getElementById("btn-goto-step3"),
    btnDirectSyncStep2: document.getElementById("btn-direct-sync-step2"),




    btnTestAttrsOnly: document.getElementById("btn-test-attrs-only"),
    btnAutoMatch: document.getElementById("btn-auto-match"),
    selectMappingProfile: document.getElementById("select-mapping-profile"),
    btnSaveMappingModal: document.getElementById("btn-save-mapping-modal"),
    modalSaveMapping: document.getElementById("modal-save-mapping"),
    inputMappingFilename: document.getElementById("input-mapping-filename"),
    btnConfirmSaveMapping: document.getElementById("btn-confirm-save-mapping"),
    inputSearchFields: document.getElementById("input-search-fields"),
    mappingCountTag: document.getElementById("mapping-count-tag"),
    mappingTbody: document.getElementById("mapping-tbody"),
    btnDirectUpdate: document.getElementById("btn-direct-update"),
    btnGotoStep4: document.getElementById("btn-goto-step4"),

    modalTestAttrsResult: document.getElementById("modal-test-attrs-result"),
    testAttrsTableName: document.getElementById("test-attrs-table-name"),
    testAttrsCount: document.getElementById("test-attrs-count"),
    testAttrsIdsList: document.getElementById("test-attrs-ids-list"),



    selectPrimaryCol: document.getElementById("select-primary-col"),
    btnPreviewMatches: document.getElementById("btn-preview-matches"),
    matchResultsBox: document.getElementById("match-results-box"),
    statJsonCount: document.getElementById("stat-json-count"),
    statMatchedCount: document.getElementById("stat-matched-count"),
    statUnmatchedCount: document.getElementById("stat-unmatched-count"),
    diffTbody: document.getElementById("diff-tbody"),
    checkAllMatches: document.getElementById("check-all-matches"),
    checkDryRun: document.getElementById("check-dry-run"),
    btnExecuteSync: document.getElementById("btn-execute-sync"),
    syncProgressBox: document.getElementById("sync-progress-box"),
    syncProgressFill: document.getElementById("sync-progress-fill"),
    syncStatusMsg: document.getElementById("sync-status-msg")
  };

  // --- INITIALIZATION ---
  init();

  async function init() {
    setupEventListeners();
    await checkTokenStatus();
    await loadWorkspacesAndBases();
    await loadFolderJsonFiles();
    await loadSavedMappingProfiles();
  }

  // --- API CALLS & STATUS ---
  async function checkTokenStatus() {
    try {
      const res = await fetch("/api/status");
      const data = await res.json();
      if (data.has_key) {
        elements.keyStatusPill.className = "status-pill online";
        elements.keyStatusText.textContent = `Token Connected (${data.masked_key})`;
      } else {
        elements.keyStatusPill.className = "status-pill offline";
        elements.keyStatusText.textContent = "Token Required";
        openModal(elements.modalSettings);
      }
    } catch (e) {
      console.error("Failed to check status", e);
    }
  }

  async function loadWorkspacesAndBases() {
    try {
      const res = await fetch("/api/workspaces-and-bases");
      const data = await res.json();
      if (data.error) throw new Error(data.error);

      state.bases = data.bases || [];
      state.workspaces = data.workspaces || [];
      state.baseToWsp = data.base_to_wsp || {};

      populateWorkspacesDropdown();
      populateBasesDropdown();
    } catch (e) {
      console.error("Error loading bases", e);
    }
  }

  function populateWorkspacesDropdown() {
    elements.selectWorkspace.innerHTML = '<option value="all">-- All Workspaces --</option>';
    state.workspaces.forEach(wsp => {
      const opt = document.createElement("option");
      opt.value = wsp.id;
      const count = (wsp.baseIds || []).length;
      opt.textContent = `${wsp.name || wsp.id} (${count} bases)`;
      elements.selectWorkspace.appendChild(opt);
    });
  }

  function populateBasesDropdown() {
    elements.selectBase.innerHTML = '<option value="">-- Select Base --</option>';
    let filtered = state.bases;
    if (state.selectedWorkspace !== "all") {
      const targetWsp = state.workspaces.find(w => w.id === state.selectedWorkspace);
      if (targetWsp && targetWsp.baseIds) {
        const baseSet = new Set(targetWsp.baseIds);
        filtered = state.bases.filter(b => baseSet.has(b.id));
      }
    }

    filtered.forEach(base => {
      const opt = document.createElement("option");
      opt.value = base.id;
      const wspTag = state.baseToWsp[base.id] ? ` [${state.baseToWsp[base.id]}]` : '';
      opt.textContent = `${base.name}${wspTag}`;
      elements.selectBase.appendChild(opt);
    });

    elements.selectBase.disabled = filtered.length === 0;
  }

  async function loadTablesForBase(baseId) {
    elements.selectTable.innerHTML = '<option value="">Loading Tables...</option>';
    elements.selectTable.disabled = true;
    if (elements.selectAttrTable) {
      elements.selectAttrTable.innerHTML = '<option value="">Loading Tables...</option>';
      elements.selectAttrTable.disabled = true;
    }
    elements.schemaPreview.classList.add("hidden");

    try {
      const res = await fetch(`/api/tables?base_id=${baseId}`);
      const data = await res.json();
      if (data.error) throw new Error(data.error);

      elements.selectTable.innerHTML = '<option value="">-- Select Products Table --</option>';
      if (elements.selectAttrTable) {
        elements.selectAttrTable.innerHTML = '<option value="">-- None / Select Attributes Table --</option>';
      }

      let autoAttrTable = "";

      data.tables.forEach(tbl => {
        const opt = document.createElement("option");
        opt.value = tbl.name;
        opt.dataset.fields = JSON.stringify(tbl.fields || []);
        opt.textContent = `${tbl.name} (${(tbl.fields || []).length} cols)`;
        elements.selectTable.appendChild(opt);

        if (elements.selectAttrTable) {
          const optAttr = document.createElement("option");
          optAttr.value = tbl.name;
          optAttr.textContent = tbl.name;
          const tLower = tbl.name.toLowerCase();
          if (tLower === "product attributes" || tLower === "product attribute" || tLower === "attributes") {
            optAttr.selected = true;
            autoAttrTable = tbl.name;
          }
          elements.selectAttrTable.appendChild(optAttr);
        }
      });

      elements.selectTable.disabled = false;
      if (elements.selectAttrTable) {
        elements.selectAttrTable.disabled = false;
      }
      state.selectedAttrTableName = autoAttrTable;
    } catch (e) {
      alert("Failed to load tables: " + e.message);
    }
  }


  async function loadFolderJsonFiles() {
    try {
      const res = await fetch("/api/json-files");
      const data = await res.json();
      if (!elements.jsonFilesChecklist) return;

      elements.jsonFilesChecklist.innerHTML = "";

      const files = data.files || [];

      if (files.length > 0) {
        files.forEach((f, idx) => {
          const itemDiv = document.createElement("div");
          itemDiv.className = "json-file-item";
          itemDiv.style.cssText = "display:flex; align-items:center; gap:8px; padding:8px 12px; background:rgba(255,255,255,0.03); border:1px solid var(--border-light); border-radius:6px;";

          const chkId = `chk-json-file-${idx}`;
          const formattedSize = (f.size / 1024).toFixed(1) + " KB";

          let icon = "📄";
          const lowerName = f.name.toLowerCase();
          if (lowerName.endsWith(".xlsx") || lowerName.endsWith(".xls")) icon = "📊";
          else if (lowerName.endsWith(".csv")) icon = "📈";

          itemDiv.innerHTML = `
            <input type="checkbox" class="json-file-chk" data-path="${escapeHtml(f.name)}" id="${chkId}" style="width:16px; height:16px; cursor:pointer;">
            <label for="${chkId}" style="font-size:13px; font-weight:500; cursor:pointer; flex-grow:1; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="${escapeHtml(f.name)}">
              ${icon} ${escapeHtml(f.name)}
            </label>
            <span style="font-size:11px; color:var(--muted);">${formattedSize}</span>
          `;


          elements.jsonFilesChecklist.appendChild(itemDiv);
        });

        // Add event listeners to individual checkboxes
        const checkboxes = elements.jsonFilesChecklist.querySelectorAll(".json-file-chk");
        checkboxes.forEach(chk => {
          chk.addEventListener("change", updateSelectedJsonFilesCount);
        });

        if (elements.chkSelectAllJson) {
          elements.chkSelectAllJson.checked = false;
          elements.chkSelectAllJson.addEventListener("change", (e) => {
            const isChecked = e.target.checked;
            checkboxes.forEach(c => c.checked = isChecked);
            updateSelectedJsonFilesCount();
          });
        }

        updateSelectedJsonFilesCount();
      } else {

        elements.jsonFilesChecklist.innerHTML = '<div style="color:var(--muted); padding:10px; font-style:italic;">No JSON files in drop folder (json_files/).</div>';
        if (elements.jsonFilesSelectedTag) {
          elements.jsonFilesSelectedTag.textContent = "0 Selected";
        }
      }
    } catch (e) {
      console.error("Failed to list folder json files", e);
    }
  }

  function updateSelectedJsonFilesCount() {
    if (!elements.jsonFilesChecklist || !elements.jsonFilesSelectedTag) return;
    const count = elements.jsonFilesChecklist.querySelectorAll(".json-file-chk:checked").length;
    elements.jsonFilesSelectedTag.textContent = `${count} File(s) Selected`;
  }


  async function loadSavedMappingProfiles() {
    try {
      const res = await fetch("/api/mappings");
      const data = await res.json();
      elements.selectMappingProfile.innerHTML = '<option value="">-- Load Saved Profile --</option>';
      (data.profiles || []).forEach(name => {
        const opt = document.createElement("option");
        opt.value = name;
        opt.textContent = name;
        elements.selectMappingProfile.appendChild(opt);
      });
    } catch (e) {
      console.error("Failed to load mapping profiles", e);
    }
  }

  // --- EVENT LISTENERS & WIZARD FLOW ---
  function setupEventListeners() {
    // Stepper Tab Navigation
    document.querySelectorAll(".step-tab").forEach(tab => {
      tab.addEventListener("click", () => {
        const targetStep = parseInt(tab.dataset.step);
        if (targetStep <= state.currentStep || canNavigateToStep(targetStep)) {
          goToStep(targetStep);
        }
      });
    });

    document.querySelectorAll(".btn-back").forEach(btn => {
      btn.addEventListener("click", () => {
        goToStep(parseInt(btn.dataset.target));
      });
    });

    // Token Settings
    elements.btnSettings.addEventListener("click", () => openModal(elements.modalSettings));
    elements.btnSaveToken.addEventListener("click", async () => {
      const token = elements.inputPatToken.value.strip();
      if (!token) return alert("Please enter a token");
      const res = await fetch("/api/save-token", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ token })
      });
      const data = await res.json();
      if (data.success) {
        closeModal(elements.modalSettings);
        checkTokenStatus();
        loadWorkspacesAndBases();
      }
    });

    // Step 1: Base & Table Selection
    elements.selectWorkspace.addEventListener("change", (e) => {
      state.selectedWorkspace = e.target.value;
      populateBasesDropdown();
    });

    elements.selectBase.addEventListener("change", (e) => {
      state.selectedBaseId = e.target.value;
      state.selectedTableName = "";
      elements.btnGotoStep2.disabled = true;
      if (state.selectedBaseId) {
        loadTablesForBase(state.selectedBaseId);
      }
    });

    elements.selectTable.addEventListener("change", (e) => {
      state.selectedTableName = e.target.value;
      if (state.selectedTableName) {
        const selectedOpt = e.target.selectedOptions[0];
        const fields = JSON.parse(selectedOpt.dataset.fields || "[]");
        state.tableColumns = fields.map(f => f.name);
        renderTableSchemaPills(state.tableColumns);
        elements.btnGotoStep2.disabled = false;
        populatePrimaryColumnDropdown();
      } else {
        elements.btnGotoStep2.disabled = true;
      }
    });

    if (elements.selectAttrTable) {
      elements.selectAttrTable.addEventListener("change", (e) => {
        state.selectedAttrTableName = e.target.value;
      });
    }


    elements.btnGotoStep2.addEventListener("click", () => goToStep(2));

    // Step 2: JSON Handling
    elements.dropzone.addEventListener("click", () => elements.fileInput.click());
    elements.dropzone.addEventListener("dragover", (e) => {
      e.preventDefault();
      elements.dropzone.classList.add("dragover");
    });
    elements.dropzone.addEventListener("dragleave", () => elements.dropzone.classList.remove("dragover"));
    elements.dropzone.addEventListener("drop", (e) => {
      e.preventDefault();
      elements.dropzone.classList.remove("dragover");
      if (e.dataTransfer.files.length > 0) {
        uploadJsonFile(e.dataTransfer.files[0]);
      }
    });

    elements.fileInput.addEventListener("change", (e) => {
      if (e.target.files.length > 0) {
        uploadJsonFile(e.target.files[0]);
      }
    });

    if (elements.btnLoadFolderJson) {
      elements.btnLoadFolderJson.addEventListener("click", async () => {
        const checkedInputs = elements.jsonFilesChecklist ? elements.jsonFilesChecklist.querySelectorAll(".json-file-chk:checked") : [];
        if (checkedInputs.length === 0) return alert("Please check at least one JSON file to load.");
        const selectedPaths = Array.from(checkedInputs).map(chk => chk.dataset.path);
        parseJsonFromPaths(selectedPaths);
      });
    }


    if (elements.btnToggleViewTable) {
      elements.btnToggleViewTable.addEventListener("click", () => {
        if (elements.previewTableBox) elements.previewTableBox.classList.remove("hidden");
        if (elements.previewJsonBox) elements.previewJsonBox.classList.add("hidden");
        elements.btnToggleViewTable.className = "btn btn-xs btn-accent";
        elements.btnToggleViewJson.className = "btn btn-xs btn-outline";
      });
    }

    if (elements.btnToggleViewJson) {
      elements.btnToggleViewJson.addEventListener("click", () => {
        if (elements.previewJsonBox) elements.previewJsonBox.classList.remove("hidden");
        if (elements.previewTableBox) elements.previewTableBox.classList.add("hidden");
        elements.btnToggleViewJson.className = "btn btn-xs btn-accent";
        elements.btnToggleViewTable.className = "btn btn-xs btn-outline";
      });
    }

    if (elements.btnDirectSyncStep2) {
      elements.btnDirectSyncStep2.addEventListener("click", performStep2DirectSync);
    }


    if (elements.btnGotoStep3) {
      elements.btnGotoStep3.addEventListener("click", () => {
        renderMappingStudioTable();
        goToStep(3);
      });
    }


    // Step 3: Mapping Studio
    if (elements.btnTestAttrsOnly) {
      elements.btnTestAttrsOnly.addEventListener("click", performTestAttrsOnly);
    }
    elements.btnAutoMatch.addEventListener("click", performAutoMatch);
    elements.inputSearchFields.addEventListener("input", filterMappingRows);
    elements.selectMappingProfile.addEventListener("change", async (e) => {
      const profileName = e.target.value;
      if (profileName) {
        await loadMappingProfile(profileName);
      }
    });

    elements.btnSaveMappingModal.addEventListener("click", () => openModal(elements.modalSaveMapping));
    elements.btnConfirmSaveMapping.addEventListener("click", saveCurrentMappingProfile);
    elements.btnDirectUpdate.addEventListener("click", performDirectSyncUpdate);
    if (elements.btnGotoStep4) {
      elements.btnGotoStep4.addEventListener("click", () => goToStep(4));
    }




    // Step 4: Sync & Match
    elements.btnPreviewMatches.addEventListener("click", generateMatchPreview);
    elements.btnExecuteSync.addEventListener("click", executeSyncUpdate);

    // Modal Close
    document.querySelectorAll(".modal-close").forEach(btn => {
      btn.addEventListener("click", (e) => {
        closeModal(e.target.closest(".modal"));
      });
    });
  }

  // --- STEP 1 SCHEMAS & UTILS ---
  function renderTableSchemaPills(cols) {
    elements.colCount.textContent = cols.length;
    elements.tableColsPills.innerHTML = "";
    cols.forEach(col => {
      const pill = document.createElement("span");
      pill.className = "pill";
      pill.textContent = col;
      elements.tableColsPills.appendChild(pill);
    });
    elements.schemaPreview.classList.remove("hidden");
  }

  function populatePrimaryColumnDropdown() {
    elements.selectPrimaryCol.innerHTML = '<option value="">-- Select Matching Column --</option>';
    state.tableColumns.forEach(col => {
      const opt = document.createElement("option");
      opt.value = col;
      opt.textContent = col;
      if (col.toLowerCase() === "product code" || col.toLowerCase() === "product_code" || col.toLowerCase() === "sku code" || col.toLowerCase() === "sku" || col.toLowerCase() === "name") {
        opt.selected = true;
      }
      elements.selectPrimaryCol.appendChild(opt);
    });
  }

  // --- STEP 2 JSON PARSING ---
  async function uploadJsonFile(file) {
    const formData = new FormData();
    formData.append("file", file);
    try {
      const res = await fetch("/api/parse-json", {
        method: "POST",
        body: formData
      });
      const data = await res.json();
      if (data.error) throw new Error(data.error);
      setParsedJson(data);
    } catch (e) {
      alert("Error parsing uploaded JSON: " + e.message);
    }
  }

  async function parseJsonFromPaths(targetPaths) {
    try {
      const pathsArr = Array.isArray(targetPaths) ? targetPaths : [targetPaths];
      const res = await fetch("/api/parse-json", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ paths: pathsArr })
      });
      const data = await res.json();
      if (data.error) throw new Error(data.error);
      setParsedJson(data);
    } catch (e) {
      alert("Error parsing JSON files: " + e.message);
    }
  }

  async function parseJsonFromPath(targetPath) {
    return parseJsonFromPaths([targetPath]);
  }


  function setParsedJson(data) {
    state.jsonPayload = data;
    if (elements.jsonItemsCount) elements.jsonItemsCount.textContent = `${data.total_items} Product Item(s) Parsed`;
    if (elements.jsonKeysCount) elements.jsonKeysCount.textContent = `${(data.keys || []).length} Unique Columns`;
    if (elements.jsonPreviewCode) elements.jsonPreviewCode.textContent = JSON.stringify(data.sample || {}, null, 2);
    if (elements.jsonParsedSummary) elements.jsonParsedSummary.classList.remove("hidden");

    renderParsedPreviewTable(data);

    if (elements.btnGotoStep3) elements.btnGotoStep3.disabled = false;
    if (elements.btnTestAttrsOnly) elements.btnTestAttrsOnly.disabled = false;
    if (elements.btnDirectSyncStep2) elements.btnDirectSyncStep2.disabled = false;
  }


  function renderParsedPreviewTable(data) {
    if (!elements.previewTheadRow || !elements.previewTbody) return;

    elements.previewTheadRow.innerHTML = "";
    elements.previewTbody.innerHTML = "";

    const items = data.items || [];
    if (items.length === 0) return;

    // Priority column order for display: #, Source File, Product Code, SKU Code, SKU, Name, Product type, Product description, Categories, Status, Attributes, Options, Constraints, then others
    const priorityCols = ["Product Code", "SKU Code", "SKU", "Name", "Product type", "Product Type", "Product description", "Categories", "Status", "Attributes", "product_features", "Options", "options", "Constraints", "constraints"];

    const allKeys = data.keys || [];
    const displayCols = ["#", "Source File"];

    priorityCols.forEach(col => {
      if (allKeys.includes(col) && !displayCols.includes(col)) {
        displayCols.push(col);
      }
    });

    allKeys.forEach(col => {
      if (!displayCols.includes(col)) {
        displayCols.push(col);
      }
    });

    const finalCols = displayCols.slice(0, 12);

    // Render Table Header
    finalCols.forEach(col => {
      const th = document.createElement("th");
      th.style.cssText = "padding: 8px 12px; border-right: 1px solid var(--border-light); font-weight: 600; white-space: nowrap;";
      th.textContent = col;
      elements.previewTheadRow.appendChild(th);
    });

    // Render Table Rows (up to 100 items)
    items.slice(0, 100).forEach((item, idx) => {
      const tr = document.createElement("tr");
      tr.style.cssText = "border-bottom: 1px solid var(--border-light);";

      const flat = item._flat || {};
      const raw = item._raw || {};

      finalCols.forEach(col => {
        const td = document.createElement("td");
        td.style.cssText = "padding: 8px 12px; border-right: 1px solid var(--border-light); max-width: 240px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;";

        if (col === "#") {
          td.textContent = idx + 1;
          td.style.fontWeight = "600";
          td.style.color = "var(--muted)";
        } else if (col === "Source File") {
          td.textContent = item._source_file || "File";
        } else {
          let val = flat[col] !== undefined ? flat[col] : raw[col];
          if (val === undefined || val === null) {
            val = "";
          } else if (typeof val === "object") {
            val = JSON.stringify(val);
          }
          td.textContent = val;
          td.title = String(val);
        }

        tr.appendChild(td);
      });

      elements.previewTbody.appendChild(tr);
    });
  }





  // --- STEP 3 MAPPING STUDIO ---
  function renderMappingStudioTable() {
    if (!state.jsonPayload || !state.jsonPayload.keys) return;

    elements.mappingTbody.innerHTML = "";

    // Sort keys logically (top level keys first, array elements collapsed/sorted)
    const sortedKeys = [...state.jsonPayload.keys].sort((a, b) => {
      const depthA = (a.match(/\./g) || []).length;
      const depthB = (b.match(/\./g) || []).length;
      return depthA - depthB || a.localeCompare(b);
    });

    sortedKeys.forEach(jKey => {
      const tr = document.createElement("tr");
      tr.dataset.jkey = jKey;

      const sampleFlat = (state.jsonPayload.items && state.jsonPayload.items[0]) ? state.jsonPayload.items[0]._flat : {};
      const sampleVal = sampleFlat[jKey];
      let previewText = "";
      if (sampleVal !== undefined && sampleVal !== null) {
        const strVal = String(sampleVal);
        previewText = strVal.length > 50 ? strVal.slice(0, 50) + "..." : strVal;
      }

      const tdKey = document.createElement("td");
      tdKey.innerHTML = `<code class="key-code">${escapeHtml(jKey)}</code>` + (previewText ? `<span class="val-preview" style="display:block; font-size:11px; color:var(--muted); margin-top:3px;">e.g. "${escapeHtml(previewText)}"</span>` : "");


      const tdDir = document.createElement("td");
      tdDir.innerHTML = `<span style="color: var(--muted);">&rarr;</span>`;

      const tdCol = document.createElement("td");
      const select = document.createElement("select");
      select.className = "form-control-sm mapping-select";
      select.dataset.jkey = jKey;

      select.innerHTML = '<option value="">-- Skip Field --</option>';
      state.tableColumns.forEach(col => {
        const opt = document.createElement("option");
        opt.value = col;
        opt.textContent = col;
        if (state.activeMapping[jKey] === col) {
          opt.selected = true;
        }
        select.appendChild(opt);
      });

      select.addEventListener("change", (e) => {
        const val = e.target.value;
        if (val) {
          state.activeMapping[jKey] = val;
        } else {
          delete state.activeMapping[jKey];
        }
        updateMappingCountTag();
      });

      tdCol.appendChild(select);
      tr.appendChild(tdKey);
      tr.appendChild(tdDir);
      tr.appendChild(tdCol);

      elements.mappingTbody.appendChild(tr);
    });

    updateMappingCountTag();
    if (elements.btnGotoStep4) {
      elements.btnGotoStep4.disabled = false;
    }
  }


  function performAutoMatch() {
    if (!state.jsonPayload || !state.jsonPayload.keys) return;

    let matchCount = 0;

    // Explicit default mappings as requested:
    // "product_features" -> "Attributes" (or "Product attributes")
    // "options" -> "Options"
    // "constraints" -> "Constraints"
    // "product_name" -> "Name"
    // "product_description" -> "Product description"
    // "SKU" -> "SKU"
    // "product_images" -> "Product image"

    const defaultPairs = [
      { jKey: "product_features", targets: ["Attributes", "Product attributes", "Product Attributes"] },
      { jKey: "options", targets: ["Options"] },
      { jKey: "constraints", targets: ["Constraints"] },
      { jKey: "product_name", targets: ["Name", "Product name", "Title"] },
      { jKey: "product_description", targets: ["Product description", "Description"] },
      { jKey: "Product Code", targets: ["Product Code", "Product_Code", "SKU Code", "SKU"] },
      { jKey: "product_code", targets: ["Product Code", "Product_Code", "SKU Code", "SKU"] },
      { jKey: "SKU Code", targets: ["Product Code", "Product_Code", "SKU Code", "SKU"] },
      { jKey: "SKU", targets: ["Product Code", "SKU"] },
      { jKey: "sku", targets: ["Product Code", "SKU"] },
      { jKey: "product_images", targets: ["Product image", "Product gallery"] }
    ];

    defaultPairs.forEach(pair => {
      if (state.jsonPayload.keys.includes(pair.jKey)) {
        for (const col of state.tableColumns) {
          if (pair.targets.some(t => t.toLowerCase() === col.toLowerCase())) {
            state.activeMapping[pair.jKey] = col;
            matchCount++;
            break;
          }
        }
      }
    });

    // Fuzzy match remaining unmapped keys
    state.jsonPayload.keys.forEach(jKey => {
      if (state.activeMapping[jKey]) return; // already mapped
      const cleanJKey = jKey.toLowerCase().replace(/[^a-z0-9]/g, "");
      state.tableColumns.forEach(col => {
        const cleanCol = col.toLowerCase().replace(/[^a-z0-9]/g, "");
        if (cleanJKey === cleanCol || cleanJKey.includes(cleanCol) || cleanCol.includes(cleanJKey)) {
          state.activeMapping[jKey] = col;
          matchCount++;
        }
      });
    });

    renderMappingStudioTable();
    alert(`Auto-matched ${matchCount} field(s)!`);
  }

  function filterMappingRows() {
    const q = elements.inputSearchFields.value.toLowerCase().strip();
    const rows = elements.mappingTbody.querySelectorAll("tr");
    rows.forEach(tr => {
      const jKey = tr.dataset.jkey.toLowerCase();
      const match = !q || jKey.includes(q);
      tr.style.display = match ? "" : "none";
    });
  }

  function updateMappingCountTag() {
    const count = Object.keys(state.activeMapping).length;
    elements.mappingCountTag.textContent = `${count} Mapped`;
    if (elements.btnDirectUpdate) {
      elements.btnDirectUpdate.disabled = count === 0;
    }
    if (elements.btnGotoStep4) {
      elements.btnGotoStep4.disabled = count === 0;
    }
  }

  async function performTestAttrsOnly() {

    if (!state.selectedBaseId) {
      return alert("Please select an Airtable Base in Step 1.");
    }
    const attrTable = state.selectedAttrTableName || "Product attributes";
    if (!attrTable) {
      return alert("Please select a Product Attributes Linked Table in Step 1.");
    }

    if (!state.jsonPayload || !state.jsonPayload.items || state.jsonPayload.items.length === 0) {
      return alert("Please load a JSON file in Step 2 first.");
    }

    const firstItem = state.jsonPayload.items[0];
    const rawData = firstItem._raw || {};
    const featuresInput = rawData.product_features || rawData.attributes || rawData;

    elements.btnTestAttrsOnly.disabled = true;
    elements.btnTestAttrsOnly.textContent = "Syncing Attributes...";

    try {
      const res = await fetch("/api/test-attributes-sync", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          base_id: state.selectedBaseId,
          attr_table_name: attrTable,
          product_features: featuresInput
        })
      });

      const data = await res.json();
      if (data.error) throw new Error(data.error);

      elements.testAttrsTableName.textContent = data.attr_table_name;
      elements.testAttrsCount.textContent = data.record_ids_count || 0;

      const idsList = data.record_ids || [];
      elements.testAttrsIdsList.innerHTML = idsList.map((id, i) => `<div>${i+1}. <code>${escapeHtml(id)}</code></div>`).join("");

      openModal(elements.modalTestAttrsResult);
    } catch (e) {
      alert("Test Attributes Sync Failed: " + e.message);
    } finally {
      elements.btnTestAttrsOnly.disabled = false;
      elements.btnTestAttrsOnly.textContent = "🧪 Test Sync Attributes Only";
    }
  }

  async function performStep2DirectSync() {

    if (!state.selectedBaseId || !state.selectedTableName) {
      return alert("Please select a Workspace, Base, and Target Products Table in Step 1.");
    }
    if (!state.jsonPayload || !state.jsonPayload.items || state.jsonPayload.items.length === 0) {
      return alert("Please select or upload a JSON file in Step 2 first.");
    }

    if (!confirm(`Sync JSON data directly to Airtable table '${state.selectedTableName}'? (Matching JSON key names directly to Airtable column names)`)) {
      return;
    }

    elements.btnDirectSyncStep2.disabled = true;
    elements.btnDirectSyncStep2.textContent = "Syncing Direct to Airtable...";

    try {
      // Step A: Preview & match existing records using direct key-to-column auto mapping
      const matchRes = await fetch("/api/preview-matches", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          base_id: state.selectedBaseId,
          table_name: state.selectedTableName,
          attr_table_name: state.selectedAttrTableName || "Product attributes",
          items: state.jsonPayload.items
        })
      });

      const matchData = await matchRes.json();
      if (matchData.error) throw new Error(matchData.error);

      const matches = matchData.matches || [];
      const unmatched = matchData.unmatched || [];

      const updates = matches.map(m => ({
        id: m.record_id,
        fields: m.fields_to_update
      }));

      const creations = unmatched.map(u => ({
        fields: u.mapped_fields
      }));

      if (updates.length === 0 && creations.length === 0) {
        alert("No items to update or create.");
        return;
      }

      // Step B: Execute batch updates & creations
      const execRes = await fetch("/api/execute-updates", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          base_id: state.selectedBaseId,
          table_name: state.selectedTableName,
          updates: updates,
          creations: creations
        })
      });

      const execData = await execRes.json();
      if (execData.error) throw new Error(execData.error);

      let msg = "✓ SUCCESS!";
      if (execData.updated_count > 0) msg += ` Updated ${execData.updated_count} record(s).`;
      if (execData.created_count > 0) msg += ` Created ${execData.created_count} new record(s).`;

      alert(msg);
    } catch (e) {
      alert("Direct Sync Failed: " + e.message);
    } finally {
      elements.btnDirectSyncStep2.disabled = false;
      elements.btnDirectSyncStep2.textContent = "⚡ Sync Direct to Airtable";
    }
  }

  async function performDirectSyncUpdate() {


    if (!state.activeMapping || Object.keys(state.activeMapping).length === 0) {
      return alert("Please map at least one field to an Airtable column.");
    }

    // Auto-detect primary key matching column (e.g. SKU, Name, Product_Name)
    let primaryCol = "";
    for (const [jKey, colName] of Object.entries(state.activeMapping)) {
      const cLower = colName.toLowerCase();
      if (cLower === "sku" || cLower === "name" || cLower === "product name" || cLower === "id") {
        primaryCol = colName;
        break;
      }
    }

    if (!primaryCol) {
      primaryCol = Object.values(state.activeMapping)[0];
    }

    if (!confirm(`Directly sync to Airtable table '${state.selectedTableName}' (matching by column '${primaryCol}')?`)) {
      return;
    }

    elements.btnDirectUpdate.disabled = true;
    elements.btnDirectUpdate.textContent = "Matching & Syncing to Airtable...";

    try {
      // Step A: Preview & match existing records
      const matchRes = await fetch("/api/preview-matches", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          base_id: state.selectedBaseId,
          table_name: state.selectedTableName,
          primary_key_col: primaryCol,
          attr_table_name: state.selectedAttrTableName,
          mapping: state.activeMapping,
          items: state.jsonPayload.items
        })
      });


      const matchData = await matchRes.json();
      if (matchData.error) throw new Error(matchData.error);

      const matches = matchData.matches || [];
      const unmatched = matchData.unmatched || [];

      const updates = matches.map(m => ({
        id: m.record_id,
        fields: m.fields_to_update
      }));

      const creations = unmatched.map(u => ({
        fields: u.mapped_fields
      }));

      if (updates.length === 0 && creations.length === 0) {
        alert("No items to update or create.");
        return;
      }

      // Step B: Execute batch PATCH updates & POST creations
      const execRes = await fetch("/api/execute-updates", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          base_id: state.selectedBaseId,
          table_name: state.selectedTableName,
          updates: updates,
          creations: creations
        })
      });

      const execData = await execRes.json();
      if (execData.error) throw new Error(execData.error);

      let msg = "✓ SUCCESS!";
      if (execData.updated_count > 0) msg += ` Updated ${execData.updated_count} record(s).`;
      if (execData.created_count > 0) msg += ` Created ${execData.created_count} new record(s).`;

      alert(msg);
    } catch (e) {
      alert("Direct Sync Failed: " + e.message);
    } finally {
      elements.btnDirectUpdate.disabled = false;
      elements.btnDirectUpdate.textContent = "⚡ Direct Sync to Airtable";
    }
  }



  async function loadMappingProfile(profileName) {
    try {
      const res = await fetch(`/api/load-mapping?name=${encodeURIComponent(profileName)}`);
      const data = await res.json();
      if (data.error) throw new Error(data.error);
      state.activeMapping = data.mapping || {};
      renderMappingStudioTable();
    } catch (e) {
      alert("Error loading profile: " + e.message);
    }
  }

  async function saveCurrentMappingProfile() {
    let filename = elements.inputMappingFilename.value.trim();
    if (!filename) filename = "product_mapping.json";
    if (!filename.endsWith(".json")) filename += ".json";

    try {
      const res = await fetch("/api/save-mapping", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          name: filename,
          mapping: state.activeMapping
        })
      });
      const data = await res.json();
      if (data.success) {
        closeModal(elements.modalSaveMapping);
        await loadSavedMappingProfiles();
        elements.selectMappingProfile.value = filename;
        alert(`Mapping profile saved to: ${filename}`);
      }
    } catch (e) {
      alert("Failed to save mapping profile: " + e.message);
    }
  }

  // --- STEP 4 MATCH & SYNC ---
  async function generateMatchPreview() {
    const primaryCol = elements.selectPrimaryCol.value;
    if (!primaryCol) return alert("Please select a primary matching column");

    elements.btnPreviewMatches.disabled = true;
    elements.btnPreviewMatches.textContent = "Fetching records...";

    try {
      const res = await fetch("/api/preview-matches", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          base_id: state.selectedBaseId,
          table_name: state.selectedTableName,
          primary_key_col: primaryCol,
          attr_table_name: state.selectedAttrTableName,
          mapping: state.activeMapping,
          items: state.jsonPayload.items
        })
      });


      const data = await res.json();
      if (data.error) throw new Error(data.error);

      state.matchPreview = data;
      renderMatchResults(data);
    } catch (e) {
      alert("Error matching records: " + e.message);
    } finally {
      elements.btnPreviewMatches.disabled = false;
      elements.btnPreviewMatches.textContent = "🔍 Fetch Existing Records & Match";
    }
  }

  function renderMatchResults(data) {
    elements.statJsonCount.textContent = data.total_json_items || 0;
    elements.statMatchedCount.textContent = (data.matches || []).length;
    elements.statUnmatchedCount.textContent = (data.unmatched || []).length;

    elements.diffTbody.innerHTML = "";

    // Matched items (for PATCH update)
    (data.matches || []).forEach((match, idx) => {
      const tr = document.createElement("tr");

      const tdChk = document.createElement("td");
      tdChk.innerHTML = `<input type="checkbox" class="match-checkbox" data-type="update" data-idx="${idx}" checked>`;

      const tdId = document.createElement("td");
      tdId.innerHTML = `<code>${match.record_id}</code> <span class="badge" style="background:rgba(103,208,78,0.15); color:var(--accent);">UPDATE</span>`;

      const tdKey = document.createElement("td");
      tdKey.textContent = match.primary_value || match.match_key;

      const tdFields = document.createElement("td");
      let fieldsHtml = "";
      Object.entries(match.fields_to_update).forEach(([col, val]) => {
        fieldsHtml += `<span class="diff-tag"><strong>${escapeHtml(col)}:</strong> <span class="new">${escapeHtml(String(val))}</span></span>`;
      });
      tdFields.innerHTML = fieldsHtml;

      tr.appendChild(tdChk);
      tr.appendChild(tdId);
      tr.appendChild(tdKey);
      tr.appendChild(tdFields);

      elements.diffTbody.appendChild(tr);
    });

    // Unmatched items (for POST creation)
    (data.unmatched || []).forEach((unm, idx) => {
      const tr = document.createElement("tr");

      const tdChk = document.createElement("td");
      tdChk.innerHTML = `<input type="checkbox" class="match-checkbox" data-type="create" data-idx="${idx}" checked>`;

      const tdId = document.createElement("td");
      tdId.innerHTML = `<span class="badge" style="background:rgba(255,167,38,0.2); color:#ffa726;">NEW RECORD</span>`;

      const tdKey = document.createElement("td");
      tdKey.textContent = unm.match_key || `Item ${idx+1} (${unm.source_file})`;

      const tdFields = document.createElement("td");
      let fieldsHtml = "";
      Object.entries(unm.mapped_fields).forEach(([col, val]) => {
        fieldsHtml += `<span class="diff-tag"><strong>${escapeHtml(col)}:</strong> <span class="new">${escapeHtml(String(val))}</span></span>`;
      });
      tdFields.innerHTML = fieldsHtml;

      tr.appendChild(tdChk);
      tr.appendChild(tdId);
      tr.appendChild(tdKey);
      tr.appendChild(tdFields);

      elements.diffTbody.appendChild(tr);
    });

    elements.matchResultsBox.classList.remove("hidden");
  }

  async function executeSyncUpdate() {
    if (!state.matchPreview) return;

    const selectedMatches = [];
    const selectedCreations = [];

    const chkUpdates = elements.diffTbody.querySelectorAll(".match-checkbox[data-type='update']:checked");
    chkUpdates.forEach(chk => {
      const idx = parseInt(chk.dataset.idx);
      const match = state.matchPreview.matches[idx];
      selectedMatches.push({
        id: match.record_id,
        fields: match.fields_to_update
      });
    });

    const chkCreations = elements.diffTbody.querySelectorAll(".match-checkbox[data-type='create']:checked");
    chkCreations.forEach(chk => {
      const idx = parseInt(chk.dataset.idx);
      const unm = state.matchPreview.unmatched[idx];
      selectedCreations.push({
        fields: unm.mapped_fields
      });
    });

    if (selectedMatches.length === 0 && selectedCreations.length === 0) {
      return alert("No records selected for update or creation.");
    }

    const isDryRun = elements.checkDryRun.checked;

    if (isDryRun) {
      alert(`[DRY RUN PREVIEW]\nSelected ${selectedMatches.length} record(s) for update and ${selectedCreations.length} new record(s) for creation. No payload was sent to Airtable.`);
      return;
    }

    if (!confirm(`Sync ${selectedMatches.length} update(s) and ${selectedCreations.length} creation(s) to Airtable base '${state.selectedTableName}'?`)) {
      return;
    }

    elements.syncProgressBox.classList.remove("hidden");
    elements.syncProgressFill.style.width = "40%";
    elements.syncStatusMsg.textContent = "Syncing batch updates & creations to Airtable...";
    elements.btnExecuteSync.disabled = true;

    try {
      const res = await fetch("/api/execute-updates", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          base_id: state.selectedBaseId,
          table_name: state.selectedTableName,
          updates: selectedMatches,
          creations: selectedCreations
        })
      });

      const data = await res.json();
      if (data.error) throw new Error(data.error);

      elements.syncProgressFill.style.width = "100%";
      let msg = "✓ SUCCESS!";
      if (data.updated_count > 0) msg += ` Updated ${data.updated_count} record(s).`;
      if (data.created_count > 0) msg += ` Created ${data.created_count} new record(s).`;

      elements.syncStatusMsg.textContent = msg;
      alert(msg);
    } catch (e) {
      elements.syncStatusMsg.textContent = `Error: ${e.message}`;
      alert("Sync failed: " + e.message);
    } finally {
      elements.btnExecuteSync.disabled = false;
    }
  }


  // --- HELPERS ---
  function goToStep(stepNum) {
    state.currentStep = stepNum;
    document.querySelectorAll(".wizard-section").forEach(sec => sec.classList.remove("active"));
    document.getElementById(`step-${stepNum}`).classList.add("active");

    document.querySelectorAll(".step-tab").forEach(tab => {
      const step = parseInt(tab.dataset.step);
      tab.classList.remove("active");
      if (step === stepNum) tab.classList.add("active");
      if (step < stepNum) tab.classList.add("completed");
    });
  }

  function canNavigateToStep(step) {
    if (step === 2) return Boolean(state.selectedBaseId && state.selectedTableName);
    if (step === 3) return Boolean(state.jsonPayload);
    if (step === 4) return Boolean(Object.keys(state.activeMapping).length > 0);
    return true;
  }

  function openModal(modalEl) {
    modalEl.classList.add("open");
  }
  function closeModal(modalEl) {
    modalEl.classList.remove("open");
  }
  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }
});
