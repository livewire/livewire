/**
 * Features where order matters:
 */
import './supportListeners';
import './supportScriptsAndAssets';
import './supportJsEvaluation';
import './supportMorphDom';
import './supportDispatches';

/**
 * Features where order does NOT matter:
 */
import './supportDisablingFormsDuringRequest';
import './supportFileDownloads';
import './supportFileUploads';
import './supportQueryString';
import './supportLaravelEcho';
import './supportStreaming';
import './supportRedirects';
// import './supportIslands';
import './supportNavigate';
import './supportEntangle';
import './supportSlots';

// V4-specific features
import './supportDataLoading';
import './supportPaginators';
import './supportPreserveScroll';
import './supportWireIntersect';
// import './supportWireIsland';
import './supportJsModules';
