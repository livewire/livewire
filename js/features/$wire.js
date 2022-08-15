import { closestComponent } from "../lifecycle";

export default function () {
    Alpine.magic('wire', el => closestComponent(el).$wire)
}
