(() => {
  "use strict";
  const react = window.React,
      registry = window.wc.wcBlocksRegistry,
      html = window.wp.htmlEntities,
      s = (0, window.wp.i18n.__)("Klump ", "klump"),
      titleContent = ({ title }) => (0, html.decodeEntities)(title) || s,
      descriptionContent = ({ description }) => (0, html.decodeEntities)(description || ""),
      n = ({ logoUrls, label }) =>
          (0, react.createElement)(
              "div",
              { style: { display: "flex", flexDirection: "row", gap: "0.5rem", flexWrap: "wrap" } },
              logoUrls.map((img, index) => (0, react.createElement)("img", { key: index, src: img, alt: label }))
          ),
      settings = (0, window.wc.wcSettings.getSetting)("klump_data", {}),
      label = titleContent({ title: settings.title }),
      blockGateway = {
        name: "klump",
        label: (0, react.createElement)(
            ({ logoUrls, title }) =>
                (0, react.createElement)(
                    react.Fragment,
                    null,
                    (0, react.createElement)(
                        "div",
                        { style: { display: "flex", flexDirection: "row", gap: "0.5rem" } },
                        (0, react.createElement)("div", null, titleContent({ title: title })),
                        (0, react.createElement)(n, { logoUrls: logoUrls, label: titleContent({ title: title }) })
                    )
                ),
            { logoUrls: settings.logo_urls, title: label }
        ),
        content: (0, react.createElement)(descriptionContent, { description: settings.description }),
        edit: (0, react.createElement)(descriptionContent, { description: settings.description }),
        canMakePayment: () => true,
        ariaLabel: label,
        supports: { features: settings.supports },
      };
  (0, registry.registerPaymentMethod)(blockGateway);
})();
