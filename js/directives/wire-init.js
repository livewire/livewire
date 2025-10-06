import { directive } from "@/directives"
import { evaluateActionExpression } from '../evaluator'
import { setNextActionOrigin } from "@/request"

directive('init', ({ component, el, directive }) => {
    let fullMethod = directive.expression ? directive.expression : '$refresh'

    setNextActionOrigin({ el, directive })

    evaluateActionExpression(component, el, fullMethod)
})
