export default class {
    constructor({ data, config, api, readOnly }) {
        this._data = this.normalizeData(data);
    }

    normalizeData(data) {
        const newData = {};

        if (typeof data !== "object") {
            data = {};
        }

        newData.text = data.text || "";

        return newData;
    }

    static get toolbox() {
        return {
            title: "Text",
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M2.67 7.17V5.35c0-1.15.93-2.07 2.07-2.07h14.52c1.15 0 2.07.93 2.07 2.07v1.82M12 20.72V4.11M8.06 20.72h7.88" stroke="#FF8A65" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>',
        };
    }

    render() {
        const tag = document.createElement("p");
        tag.contentEditable = true;
        tag.classList.add("focus:outline-none");
        tag.innerHTML = this._data.text || "";
        return tag;
    }

    // renderSettings() {
    //     const settings = [
    //         {
    //             title: "withBorder",
    //             name: "withBorder",
    //             icon: `<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M15.8 10.592v2.043h2.35v2.138H15.8v2.232h-2.25v-2.232h-2.4v-2.138h2.4v-2.28h2.25v.237h1.15-1.15zM1.9 8.455v-3.42c0-1.154.985-2.09 2.2-2.09h4.2v2.137H4.15v3.373H1.9zm0 2.137h2.25v3.325H8.3v2.138H4.1c-1.215 0-2.2-.936-2.2-2.09v-3.373zm15.05-2.137H14.7V5.082h-4.15V2.945h4.2c1.215 0 2.2.936 2.2 2.09v3.42z"/></svg>`,
    //         },
    //     ];
    //     const wrapper = document.createElement("div");

    //     settings.forEach((tune) => {
    //         let button = document.createElement("div");

    //         button.classList.add("cdx-settings-button");
    //         button.innerHTML = tune.icon;
    //         wrapper.appendChild(button);
    //     });

    //     return wrapper;
    // }

    save(blockContent) {
        return {
            text: blockContent.innerHTML,
        };
    }
}
