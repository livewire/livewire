import morphDom from "./morphDom";
import wireModel from "./wireModel";
import wireWildcard from "./wireWildcard";
import hotReloading from "./../../src/Features/SupportHotReloading/SupportHotReloading";
import supportEntangle from "./../../src/Features/SupportEntangle/SupportEntangle";
import supportFileDownloads from "./../../src/Features/SupportFileDownloads/SupportFileDownloads";
import wireLoading from "./wireLoading";
import wireInit from "./wireInit";
import wirePoll from "./wirePoll";
import wireParent from "./wireParent";
import wireTransition from "./wireTransition";
import wireNavigate from "./wireNavigate";
import wireTarget from "./wireTarget";
import $wire from "./$wire";
import props from "./props";
import wireDirty from "./wireDirty";
import wireIgnore from "./wireIgnore";
import disableFormsDuringRequest from "./disableFormsDuringRequest";
import dispatchBrowserEvents from "./dispatchBrowserEvents";
import queryString from "./queryString";
import magicMethods from "./magicMethods";
import events from "./events";
import forceUpdateDirtyInputs from "./forceUpdateDirtyInputs";

export default function (enabledFeatures) {
    // wireTarget()
    $wire(enabledFeatures)
    // props(enabledFeatures)
    morphDom(enabledFeatures)
    wireModel(enabledFeatures)
    events()
    forceUpdateDirtyInputs()
    supportFileDownloads()
    // wireParent(enabledFeatures)
    // wirePoll(enabledFeatures)
    wireLoading(enabledFeatures)
    // wireTransition(enabledFeatures)
    // wireNavigate(enabledFeatures)
    wireWildcard(enabledFeatures)
    magicMethods()
    dispatchBrowserEvents()
    // hotReloading(enabledFeatures)
    disableFormsDuringRequest(enabledFeatures)
    supportEntangle()
    wireDirty()
    wireIgnore()
    wireInit()
    // queryString()
}
