import morphDom from "./morphDom";
import wireModel from "./wireModel";
import wireWildcard from "./wireWildcard";
import wireLoading from "./wireLoading";
import wireTarget from "./wireTarget";
import $wire from "./$wire";
import wireDirty from "./wireDirty";
import wireIgnore from "./wireIgnore";
import disableFormsDuringRequest from "./disableFormsDuringRequest";
import queryString from "./queryString";

export default function () {
    // wireTarget()
    $wire()
    morphDom()
    wireModel()
    wireLoading()
    wireWildcard()
    // wireDirty()
    // wireIgnore()
    // disableFormsDuringRequest()
    // queryString()
}