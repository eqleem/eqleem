/**
 * Skipped minification because the original files appears to be already minified.
 * Original file: /npm/@twind/preset-typography@1.0.7/preset-typography.global.js
 *
 * Do NOT use SRI with dynamically generated files! More information: https://www.jsdelivr.com/using-sri-with-dynamic-files
 */
(this.twind = this.twind || {}),
    (this.twind.presetTypography = (function (t) {
        "use strict";
        function o() {
            return (o =
                Object.assign ||
                function (t) {
                    for (var o = 1; o < arguments.length; o++) {
                        var e = arguments[o];
                        for (var r in e)
                            Object.prototype.hasOwnProperty.call(e, r) &&
                                (t[r] = e[r]);
                    }
                    return t;
                }).apply(this, arguments);
        }
        function e() {
            return o.apply(this, arguments);
        }
        function r(t, o, e) {
            let r = {};
            for (let n in e) r[i(t, n, o, (e) => `.${o.e(o.h(t))}${e}`)] = e[n];
            return r;
        }
        function i(t, o, { e, h: r }, i) {
            return o.replace(
                /^[^>:]+$|(>)?((?:[^:,]+(?::[\w-]+)?)|:[\w-]+)(::[\w-]+)?/g,
                (o, n = " ", l = o, a = "") =>
                    i(
                        `${n}:where(${l}):not(:where(.${e(
                            r("not-" + t)
                        )} *))${a}`
                    )
            );
        }
        function n(t) {
            return t
                ? "string" == typeof t
                    ? { fontSize: t }
                    : e(
                          { fontSize: t[0] },
                          "string" == typeof t[1] ? { lineHeight: t[1] } : t[1]
                      )
                : void 0;
        }
        function l(t, o) {
            return `${(t / o).toFixed(3).replace(/^0|\.?0+$/g, "")}em`;
        }
        return function ({
            className: o = "prose",
            defaultColor: a = "gray",
            extend: d = {},
            colors: p = {},
        } = {}) {
            return (
                (p = e(
                    {
                        body: "700",
                        headings: "900",
                        lead: "600",
                        links: "900",
                        bold: "900",
                        counters: "500",
                        bullets: "300",
                        hr: "200",
                        quotes: "900",
                        "quote-borders": "200",
                        captions: "500",
                        code: "900",
                        "pre-code": "200",
                        "pre-bg": "800",
                        "th-borders": "300",
                        "td-borders": "200",
                    },
                    p,
                    {
                        dark:
                            null === p.dark
                                ? null
                                : e(
                                      {
                                          body: "300",
                                          headings: "#fff",
                                          lead: "400",
                                          links: "#fff",
                                          bold: "#fff",
                                          counters: "400",
                                          bullets: "600",
                                          hr: "700",
                                          quotes: "100",
                                          "quote-borders": "700",
                                          captions: "400",
                                          code: "#fff",
                                          "pre-code": "300",
                                          "pre-bg": "rgb(0 0 0 / 50%)",
                                          "th-borders": "600",
                                          "td-borders": "700",
                                      },
                                      p.dark
                                  ),
                    }
                )),
                {
                    variants: [
                        ["headings", "h1,h2,h3,h4,h5,h6,th"],
                        ["h1"],
                        ["h2"],
                        ["h3"],
                        ["h4"],
                        ["h5"],
                        ["h6"],
                        ["p"],
                        ["a"],
                        ["blockquote"],
                        ["figure"],
                        ["figcaption"],
                        ["strong"],
                        ["em"],
                        ["code"],
                        ["pre"],
                        ["ol"],
                        ["ul"],
                        ["li"],
                        ["table"],
                        ["thead"],
                        ["tr"],
                        ["th"],
                        ["td"],
                        ["img"],
                        ["video"],
                        ["hr"],
                        ["lead", ".lead"],
                    ].map(([t, e = t]) => [
                        `${o}-${t}`,
                        (t, r) =>
                            i(
                                o,
                                "." == e[0] ? "." + r.e(r.h(e.slice(1))) : e,
                                r,
                                (t) => `& :is(${t.trim()})`
                            ),
                    ]),
                    rules: [
                        [
                            `(lead|not-${o})`,
                            ({ 1: t }, { h: o }) => [{ c: o(t) }],
                        ],
                        [
                            `${o}-invert`,
                            {
                                "@layer base": {
                                    "--tw-prose-body":
                                        "var(--tw-prose-invert-body)",
                                    "--tw-prose-headings":
                                        "var(--tw-prose-invert-headings)",
                                    "--tw-prose-lead":
                                        "var(--tw-prose-invert-lead)",
                                    "--tw-prose-links":
                                        "var(--tw-prose-invert-links)",
                                    "--tw-prose-bold":
                                        "var(--tw-prose-invert-bold)",
                                    "--tw-prose-counters":
                                        "var(--tw-prose-invert-counters)",
                                    "--tw-prose-bullets":
                                        "var(--tw-prose-invert-bullets)",
                                    "--tw-prose-hr":
                                        "var(--tw-prose-invert-hr)",
                                    "--tw-prose-quotes":
                                        "var(--tw-prose-invert-quotes)",
                                    "--tw-prose-quote-borders":
                                        "var(--tw-prose-invert-quote-borders)",
                                    "--tw-prose-captions":
                                        "var(--tw-prose-invert-captions)",
                                    "--tw-prose-code":
                                        "var(--tw-prose-invert-code)",
                                    "--tw-prose-pre-code":
                                        "var(--tw-prose-invert-pre-code)",
                                    "--tw-prose-pre-bg":
                                        "var(--tw-prose-invert-pre-bg)",
                                    "--tw-prose-th-borders":
                                        "var(--tw-prose-invert-th-borders)",
                                    "--tw-prose-td-borders":
                                        "var(--tw-prose-invert-td-borders)",
                                },
                            },
                        ],
                        [
                            o + "-",
                            ({ $$: t }, o) => {
                                let e = n(o.theme("fontSize", t));
                                return e && { "@layer components": e };
                            },
                        ],
                        [o + "-", ({ $$: t }, o) => s(t, o)],
                        [
                            o,
                            (t, i) =>
                                e({}, s(a, i), {
                                    "@layer base": [
                                        r(o, i, {
                                            a: {
                                                color: "var(--tw-prose-links)",
                                                textDecorationLine: "underline",
                                                fontWeight: "500",
                                            },
                                            strong: {
                                                color: "var(--tw-prose-bold)",
                                                fontWeight: "600",
                                            },
                                            "a strong,blockquote strong,thead th strong":
                                                { color: "inherit" },
                                            ul: { listStyleType: "disc" },
                                            ol: { listStyleType: "decimal" },
                                            'ol[type="A"]': {
                                                listStyleType: "upper-alpha",
                                            },
                                            'ol[type="a"]': {
                                                listStyleType: "lower-alpha",
                                            },
                                            'ol[type="A" s]': {
                                                listStyleType: "upper-alpha",
                                            },
                                            'ol[type="a" s]': {
                                                listStyleType: "lower-alpha",
                                            },
                                            'ol[type="I"]': {
                                                listStyleType: "upper-roman",
                                            },
                                            'ol[type="i"]': {
                                                listStyleType: "lower-roman",
                                            },
                                            'ol[type="I" s]': {
                                                listStyleType: "upper-roman",
                                            },
                                            'ol[type="i" s]': {
                                                listStyleType: "lower-roman",
                                            },
                                            'ol[type="1"]': {
                                                listStyleType: "decimal",
                                            },
                                            "ol,ul": {
                                                marginTop: l(20, 16),
                                                marginBottom: l(20, 16),
                                                paddingLeft: l(26, 16),
                                            },
                                            li: {
                                                marginTop: l(8, 16),
                                                marginBottom: l(8, 16),
                                            },
                                            "ol>li,ul>li": {
                                                paddingLeft: l(6, 16),
                                            },
                                            ">ul>li p": {
                                                marginTop: l(12, 16),
                                                marginBottom: l(12, 16),
                                            },
                                            ">ul>li>*:first-child,>ol>li>*:last-child":
                                                { marginTop: l(20, 16) },
                                            ">ul>li>*:last-child,>ol>li>*:last-child":
                                                { marginBottom: l(20, 16) },
                                            "ol>li::marker": {
                                                fontWeight: "400",
                                                color: "var(--tw-prose-counters)",
                                            },
                                            "ul>li::marker": {
                                                color: "var(--tw-prose-bullets)",
                                            },
                                            "ul ul,ul ol,ol ul,ol ol": {
                                                marginTop: l(12, 16),
                                                marginBottom: l(12, 16),
                                            },
                                            hr: {
                                                borderColor:
                                                    "var(--tw-prose-hr)",
                                                borderTopWidth: "1",
                                                marginTop: l(48, 16),
                                                marginBottom: l(48, 16),
                                            },
                                            blockquote: {
                                                marginTop: l(32, 20),
                                                marginBottom: l(32, 20),
                                                paddingLeft: l(20, 20),
                                                fontWeight: "500",
                                                fontStyle: "italic",
                                                color: "var(--tw-prose-quotes)",
                                                borderLeftWidth: "0.25rem",
                                                borderLeftColor:
                                                    "var(--tw-prose-quote-borders)",
                                                quotes: '"\\201C""\\201D""\\2018""\\2019"',
                                            },
                                            "blockquote p:first-of-type::before":
                                                { content: "open-quote" },
                                            "blockquote p:last-of-type::after":
                                                { content: "close-quote" },
                                            p: {
                                                marginTop: l(20, 16),
                                                marginBottom: l(20, 16),
                                            },
                                            h1: {
                                                color: "var(--tw-prose-headings)",
                                                fontWeight: "800",
                                                fontSize: l(36, 16),
                                                marginTop: "0",
                                                marginBottom: l(32, 36),
                                                lineHeight: 1.15,
                                            },
                                            "h1 strong": {
                                                fontWeight: "900",
                                                color: "inherit",
                                            },
                                            h2: {
                                                color: "var(--tw-prose-headings)",
                                                fontWeight: "700",
                                                fontSize: l(24, 16),
                                                marginTop: l(48, 24),
                                                marginBottom: l(24, 24),
                                                lineHeight: "1.35",
                                            },
                                            "h2 strong": {
                                                fontWeight: "800",
                                                color: "inherit",
                                            },
                                            h3: {
                                                color: "var(--tw-prose-headings)",
                                                fontWeight: "600",
                                                fontSize: l(20, 16),
                                                marginTop: l(32, 20),
                                                marginBottom: l(12, 20),
                                                lineHeight: "1.6",
                                            },
                                            "h3 strong": {
                                                fontWeight: "700",
                                                color: "inherit",
                                            },
                                            h4: {
                                                color: "var(--tw-prose-headings)",
                                                fontWeight: "600",
                                                marginTop: l(24, 16),
                                                marginBottom: l(8, 16),
                                                lineHeight: "1.5",
                                            },
                                            "h4 strong": {
                                                fontWeight: "700",
                                                color: "inherit",
                                            },
                                            "hr+*,h2+*,h3+*,h4+*": {
                                                marginTop: "0",
                                            },
                                            "img,video,figure": {
                                                marginTop: l(32, 16),
                                                marginBottom: l(32, 16),
                                            },
                                            "figure>*": {
                                                marginTop: "0",
                                                marginBottom: "0",
                                            },
                                            figcaption: {
                                                color: "var(--tw-prose-captions)",
                                                fontSize: l(14, 16),
                                                lineHeight: "1.4",
                                                marginTop: l(12, 14),
                                            },
                                            code: {
                                                color: "var(--tw-prose-code)",
                                                fontWeight: "600",
                                                fontSize: l(14, 16),
                                            },
                                            "code::before,code::after": {
                                                content: '"`"',
                                            },

                                            "h2 code": { fontSize: l(21, 24) },
                                            "h3 code": { fontSize: l(18, 20) },
                                            "a code,h1 code,h2 code,h3 code,h4 code,blockquote code,thead th code":
                                                { color: "inherit" },
                                            pre: {
                                                color: "var(--tw-prose-pre-code)",
                                                backgroundColor:
                                                    "var(--tw-prose-pre-bg)",
                                                overflowX: "auto",
                                                fontWeight: "400",
                                                fontSize: l(14, 16),
                                                lineHeight: "1.7",
                                                marginTop: l(24, 14),
                                                marginBottom: l(24, 14),
                                                borderRadius: "0.375rem",
                                                paddingTop: l(12, 14),
                                                paddingRight: l(16, 14),
                                                paddingBottom: l(12, 14),
                                                paddingLeft: l(16, 14),
                                            },
                                            "pre code": {
                                                backgroundColor: "transparent",
                                                borderWidth: "0",
                                                borderRadius: "0",
                                                padding: "0",
                                                fontWeight: "inherit",
                                                color: "inherit",
                                                fontSize: "inherit",
                                                fontFamily: "inherit",
                                                lineHeight: "inherit",
                                            },
                                            "pre code::before": {
                                                content: "none",
                                            },
                                            "pre code::after": {
                                                content: "none",
                                            },
                                            table: {
                                                width: "100%",
                                                tableLayout: "auto",
                                                textAlign: "left",
                                                marginTop: l(32, 16),
                                                marginBottom: l(32, 16),
                                                fontSize: l(14, 16),
                                                lineHeight: "1.7",
                                            },
                                            thead: {
                                                borderBottomWidth: "1px",
                                                borderBottomColor:
                                                    "var(--tw-prose-th-borders)",
                                            },
                                            "thead th": {
                                                color: "var(--tw-prose-headings)",
                                                fontWeight: "600",
                                                verticalAlign: "bottom",
                                                paddingRight: l(8, 14),
                                                paddingBottom: l(8, 14),
                                                paddingLeft: l(8, 14),
                                            },
                                            "thead th:first-child": {
                                                paddingLeft: "0",
                                            },
                                            "thead th:last-child": {
                                                paddingRight: "0",
                                            },
                                            "tbody tr": {
                                                borderBottomWidth: "1px",
                                                borderBottomColor:
                                                    "var(--tw-prose-td-borders)",
                                            },
                                            "tbody tr:last-child": {
                                                borderBottomWidth: "0",
                                            },
                                            "tbody td,tfoot td": {
                                                verticalAlign: "baseline",
                                                paddingTop: l(8, 14),
                                                paddingRight: l(8, 14),
                                                paddingBottom: l(8, 14),
                                                paddingLeft: l(8, 14),
                                            },
                                            "tbody td:first-child,tfoot td:first-child":
                                                { paddingLeft: "0" },
                                            "tbody td:last-child,tfoot td:last-child":
                                                { paddingRight: "0" },
                                            [`.${i.e(i.h("lead"))}`]: {
                                                color: "var(--tw-prose-lead)",
                                                fontSize: l(20, 16),
                                                lineHeight: "1.6",
                                                marginTop: l(24, 20),
                                                marginBottom: l(24, 20),
                                            },
                                            ">:first-child": { marginTop: "0" },
                                            ">:last-child": {
                                                marginBottom: "0",
                                            },
                                        }),
                                        r(o, i, d),
                                    ],
                                    "@layer components": e(
                                        {},
                                        n(i.theme("fontSize", "base")),
                                        {
                                            color: "var(--tw-prose-body)",
                                            maxWidth:
                                                "theme(max-w.prose, 65ch)",
                                        }
                                    ),
                                }),
                        ],
                    ],
                }
            );
            function s(o, e) {
                let r = {},
                    i = {},
                    n = (r, n, l) => {
                        let a = e.theme(`colors.${o}.${n}`, n);
                        l["--tw-prose-" + r] = t.toColorValue(a);
                        let d = l != i && e.d("colors", `${o}-${n}`, a);
                        d && (i["--tw-prose-" + r] = t.toColorValue(d));
                    };
                for (let l in p) {
                    let a = p[l];
                    "dark" != l && a && n(l, a, r);
                }
                for (let d in p.dark || {}) {
                    let s = p.dark[d];
                    s && (p.dark ? n("invert-" + d, s, r) : n(d, s, i));
                }
                return Object.keys(r).length
                    ? { "@layer defaults": { "&": r, [e.v("dark")]: i } }
                    : void 0;
            }
        };
    })(twind.core));
//# sourceMappingURL=/sm/8cffc635e3df04aaa67e21d3092d5bc5b5b9d606e1f5e1589c6b9d1db06e50ed.map

let customTwindconf = {
    presets: [twind.presetTypography()],

    rules: [
        [
            "-start-(\\d+)",
            (match) => ({ "inset-inline-start": `- ${match[1] / 4}rem` }),
        ],
        [
            "-end-(\\d+)",
            (match) => ({ "inset-inline-end": `- ${match[1] / 4}rem` }),
        ],
        [
            "-ms-(\\d+)",
            (match) => ({ "margin-inline-start": `- ${match[1] / 4}rem` }),
        ],
        [
            "-me-(\\d+)",
            (match) => ({ "margin-inline-end": `- ${match[1] / 4}rem` }),
        ],
        [
            "ms-(\\d+)",
            (match) => ({ "margin-inline-start": `${match[1] / 4}rem` }),
        ],
        [
            "me-(\\d+)",
            (match) => ({ "margin-inline-end": `${match[1] / 4}rem` }),
        ],

        [
            "ms-auto",
            () => ({ "margin-inline-start": "auto" }),
        ],
        [
            "me-auto",
            () => ({ "margin-inline-end": "auto" }),
        ],

        [
            "ps-(\\d+)",
            (match) => ({ "padding-inline-start": `${match[1] / 4}rem` }),
        ],
        [
            "pe-(\\d+)",
            (match) => ({ "padding-inline-end": `${match[1] / 4}rem` }),
        ],
        ["rounded-s", () => `rtl:rounded-r ltr:rounded-l`],
        ["start-", ({ $$ }) => `rtl:right-${$$} ltr:left-${$$}`],
        ["end-", ({ $$ }) => `rtl:left-${$$} ltr:right-${$$}`],
        ["no-scrollbar::-webkit-scrollbar", () => ({ display: "none" })],
        [
            "no-scrollbar",
            () => ({ "-ms-overflow-style": "none", "scrollbar-width": "none" }),
        ],
        ["rounded-s-", ({ $$ }) => `rtl:rounded-r-${$$} ltr:rounded-l-${$$}`],
        [
            "rounded-ts-",
            ({ $$ }) => `rtl:rounded-tr-${$$} ltr:rounded-lr-${$$}`,
        ],
        [
            "rounded-te-",
            ({ $$ }) => `rtl:rounded-tl-${$$} ltr:rounded-lr-${$$}`,
        ],
        ["rounded-e-", ({ $$ }) => `rtl:rounded-l-${$$} ltr:rounded-r-${$$}`],
        ["size-", ({ $$ }) => `w-${$$} h-${$$}`],
    ],
};
