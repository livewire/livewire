import { directive } from "@/directives"
import { evaluateActionExpression } from '../evaluator'

directive('init', ({ component, el, directive }) => {
    let fullMethod = directive.expression ?? '$refresh'

    evaluateActionExpression(component, el, fullMethod)
})
