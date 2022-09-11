import morphDom from "./morphDom";
import wireModel from "./wireModel";
import wireWildcard from "./wireWildcard";
import hotReloading from "./../../src/Features/SupportHotReloading/SupportHotReloading";
import eagerLoading from "./../../src/Features/SupportEagerLoading/SupportEagerLoading";
import wireLoading from "./wireLoading";
import wirePoll from "./wirePoll";
import wireParent from "./wireParent";
import wireTransition from "./wireTransition";
import wireTarget from "./wireTarget";
import $wire from "./$wire";
import props from "./props";
import wireDirty from "./wireDirty";
import wireIgnore from "./wireIgnore";
import disableFormsDuringRequest from "./disableFormsDuringRequest";
import queryString from "./queryString";

export default function (enabledFeatures) {
    // wireTarget()
    $wire(enabledFeatures)
    props(enabledFeatures)
    morphDom(enabledFeatures)
    wireModel(enabledFeatures)
    wireParent(enabledFeatures)
    wirePoll(enabledFeatures)
    wireLoading(enabledFeatures)
    wireTransition(enabledFeatures)
    wireWildcard(enabledFeatures)
    hotReloading(enabledFeatures)
    eagerLoading(enabledFeatures)
    // wireDirty()
    // wireIgnore()
    // disableFormsDuringRequest()
    // queryString()
}
