import { closestComponent } from "@/store";
import Alpine from 'alpinejs'

Alpine.magic('wire', el => closestComponent(el).$wire)
