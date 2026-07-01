import UppyAR from "@uppy/locales/lib/ar_SA";
import Uppy from "@uppy/core";
import Dashboard from "@uppy/dashboard";
import XHR from "@uppy/xhr-upload";
import "@uppy/core/dist/style.min.css";
import "@uppy/dashboard/dist/style.min.css";
import ImageEditor from "@uppy/image-editor";
import "@uppy/image-editor/dist/style.min.css";
import Form from "@uppy/form";
import DragDrop from "@uppy/drag-drop";

// import { Cropt } from "cropt";

window.Uppy = Uppy;
window.Dashboard = Dashboard;
window.XHR = XHR;
window.UppyAR = UppyAR;
window.ImageEditor = ImageEditor;
window.Form = Form;
window.DragDrop = DragDrop;

// window.Cropt = Cropt;

// import {
//     Livewire,
//     // Alpine,
// } from "@vendor/livewire/livewire/dist/livewire.esm.js";

// // import Clipboard from '@ryangjchandler/alpine-clipboard'

// // Alpine.plugin(Clipboard)
// window.Livewire = Livewire;
// // window.Alpine = Alpine;

// Livewire.start();

import "@nextapps-be/livewire-sortablejs";

// ckeditor5

import {
    ClassicEditor,
    Essentials,
    Bold,
    Italic,
    Font,
    Paragraph,
    BlockQuote,
    Heading,
    List,
    Link,
    Image,
    ImageCaption,
    ImageResize,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    ImageInsert,
    MediaEmbed,
    SimpleUploadAdapter,
    Underline,
    SourceEditing,
    Markdown,
    Autoformat,
    PasteFromMarkdownExperimental,
    HorizontalLine,
    AutoImage,
    CodeBlock,
    Alignment,
} from "ckeditor5";

import "ckeditor5/ckeditor5.css";

import coreTranslations from "ckeditor5/translations/ar.js";

window.ClassicEditor = ClassicEditor;
window.Essentials = Essentials;
window.Bold = Bold;
window.Italic = Italic;
window.Font = Font;
window.Paragraph = Paragraph;
window.BlockQuote = BlockQuote;
window.Heading = Heading;
window.List = List;
window.Link = Link;
window.Image = Image;
window.ImageCaption = ImageCaption;
window.ImageResize = ImageResize;
window.ImageStyle = ImageStyle;
window.ImageToolbar = ImageToolbar;
window.ImageUpload = ImageUpload;
window.MediaEmbed = MediaEmbed;
window.Underline = Underline;
window.SimpleUploadAdapter = SimpleUploadAdapter;
window.SourceEditing = SourceEditing;
window.Markdown = Markdown;
window.Autoformat = Autoformat;
window.PasteFromMarkdownExperimental = PasteFromMarkdownExperimental;
window.HorizontalLine = HorizontalLine;
window.AutoImage = AutoImage;
window.ImageInsert = ImageInsert;
window.CodeBlock = CodeBlock;
window.Alignment = Alignment;

window.coreTranslations = coreTranslations;
