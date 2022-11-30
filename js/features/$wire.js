import { closestComponent } from "../lifecycle";
import Alpine from 'alpinejs'

export default function () {
    Alpine.magic('wire', el => closestComponent(el).$wire)
}
