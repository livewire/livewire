import { closestComponent } from "../lifecycle";
import Alpine from 'alpinejs'

Alpine.magic('wire', el => closestComponent(el).$wire)
