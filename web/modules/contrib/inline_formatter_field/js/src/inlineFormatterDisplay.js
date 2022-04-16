// JavaScript to load the Ace Editor in for the format setting

(function($, Drupal, drupalSettings) {
  // Function for editor size adjustment.
  function editorSizeAdjustment(e) {
    e.preventDefault();
    if ($(".EditorWrapper.Open").length < 1) {
      $(".ButtonSize")
        .removeClass("fa-window-maximize")
        .addClass("fa-window-restore");
      $(".EditorWrapper").addClass("Open");
    } else {
      $(".ButtonSize")
        .removeClass("fa-window-restore")
        .addClass("fa-window-maximize");
      $(".EditorWrapper").removeClass("Open");
    }

    // Send the resize even so that Ace Editor will resize its height.
    window.dispatchEvent(new Event("resize"));
  }

  // Function for our Drupal behavior attach.
  function loadInlineFormatterDisplay(context) {
    // For each settings form, create the build.
    $(".AceEditorFormatterSettings", context)
      .once("ace_editor_textarea")
      .each((index, form) => {
        // Get default values.
        let aceTheme =
          drupalSettings.inline_formatter_field.ace_editor.setting.theme;
        let aceMode =
          drupalSettings.inline_formatter_field.ace_editor.setting.mode;
        const aceOptions =
          drupalSettings.inline_formatter_field.ace_editor.setting.options;
        // Get local storage values.
        const lsTheme = window.localStorage.getItem("ace_theme");
        const lsMode = window.localStorage.getItem("ace_mode");
        // Update values if local storage exists.
        if (lsTheme) {
          aceTheme = lsTheme;
          $(form)
            .find(".ace-theme-select")
            .val(lsTheme);
        }
        if (lsMode) {
          aceMode = lsMode;
          $(form)
            .find(".ace-mode-select")
            .val(lsMode);
        }
        Object.keys(aceOptions).forEach(option => {
          const lsItem = window.localStorage.getItem(`ace_${option}`);
          if (lsItem) {
            aceOptions[option] = lsItem;
            $(form)
              .find(`[data-option-name='${option}']`)
              .val(lsItem);
          }
        });

        // Ace Editor settings and set up.
        const editor = ace.edit("AceEditor");
        let aceSource =
          drupalSettings.inline_formatter_field.ace_editor.setting.ace_source;
        if (
          aceSource.substring(0, 7) !== "http://" &&
          aceSource.substring(0, 8) !== "https://"
        ) {
          aceSource = aceSource[0] === "/" ? aceSource.substring(1) : aceSource;
          aceSource = aceSource.split("/");
          aceSource.pop();
          aceSource = aceSource.join("/");
          ace.config.set(
            "basePath",
            `${window.location.protocol}//${window.location.host}${drupalSettings.path.baseUrl}${aceSource}`
          );
        }
        editor.setTheme(aceTheme);
        editor.getSession().setMode(aceMode);
        editor.getSession().setValue(
          $(form)
            .find(".AceEditorTextarea")
            .val(),
          -1
        );
        editor.getSession().on("change", () => {
          $(form)
            .find(".AceEditorTextarea")
            .val(editor.getSession().getValue());
        });
        Object.keys(aceOptions).forEach(option => {
          if (aceOptions[option].toString().toLowerCase() === "true") {
            aceOptions[option] = true;
          }
          if (aceOptions[option].toString().toLowerCase() === "false") {
            aceOptions[option] = false;
          }
          editor.setOption(option, aceOptions[option]);
        });

        // Toggle when the editor size button is clicked.
        $(".ButtonSize").click(editorSizeAdjustment);

        // When 'esc' key is pressed, exit the full screen mode.
        $(document).keyup(e => {
          if (e.key === "Escape" && $(".EditorWrapper.Open").length > 0) {
            editorSizeAdjustment(e);
          }
        });
        $(form)
          .find(".ace-theme-select")
          .on("change", e => {
            const { value } = e.target;
            window.localStorage.setItem("ace_theme", value);
            editor.setTheme(value);
          });
        $(form)
          .find(".ace-mode-select")
          .on("change", e => {
            const { value } = e.target;
            window.localStorage.setItem("ace_mode", value);
            editor.getSession().setMode(value);
          });
        $(form)
          .find(".ace-option-field")
          .on("change", e => {
            let { value } = e.target;
            if (value.toString().toLowerCase() === "true") {
              value = true;
            }
            if (value.toString().toLowerCase() === "false") {
              value = false;
            }
            const option = $(e.target).attr("data-option-name");
            window.localStorage.setItem(`ace_${option}`, value);
            editor.setOption(option, value);
          });
      });
  }

  function aceFailed() {
    $(".AceEditorTextarea").css({ display: "block" });
    $(".EditorWrapper").css({ display: "none" });
  }

  // Define our Drupal behavior.
  Drupal.behaviors.ace_editor = {};

  // Check if Ace Editor loaded first before assigning the attach function.
  if (typeof ace !== "undefined") {
    Drupal.behaviors.ace_editor.attach = loadInlineFormatterDisplay;
  } else {
    // Wait for Ace Editor to load then assign the attach function.
    let waitTime = 0;
    const waitForAce = setInterval(() => {
      if (typeof ace !== "undefined") {
        Drupal.behaviors.ace_editor.attach = loadInlineFormatterDisplay;
        Drupal.behaviors.ace_editor.attach();
        clearInterval(waitForAce);
      }
      // Manual timeout to just use the textfield.
      if (waitTime > 25) {
        Drupal.behaviors.ace_editor.attach = aceFailed;
        Drupal.behaviors.ace_editor.attach();
        clearInterval(waitForAce);
      }
      waitTime += 1;
    }, 150);
  }
})(jQuery, Drupal, drupalSettings);
