import EditorJS from "@editorjs/editorjs";
import ImageTool from "@editorjs/image";
import Delimiter from "@editorjs/delimiter";

window.EditorJS = EditorJS;
window.ImageTool = ImageTool;
window.Delimiter = Delimiter;

// catalog edirorjs plugins ..
import Paragraph from "./p2/p2";
window.P2 = Paragraph;

import Header from "./header2/header2";
window.Header2 = Header;

import AttachesTool from "@editorjs/attaches";
window.AttachesTool = AttachesTool;
