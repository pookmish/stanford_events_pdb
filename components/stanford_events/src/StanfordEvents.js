import React from 'react'
import {Router, Route, hashHistory} from 'react-router'
import {render} from 'react-dom'
import {App} from './components/App'

window.React = React


// render(
//   <Router history={hashHistory}>
//     <Route path="/" component={App}/>
//     <Route path="/list-days" component={App}>
//       <Route path=":filter" component={App}/>
//     </Route>
//     <Route path="/add-day" component={App}/>
//     <Route path="*" component={PageNotFound}/>
//   </Router>
//   , document.getElementById('stanford-events')
// );


render(
  <App />,
  document.getElementById('stanford-events')
);
