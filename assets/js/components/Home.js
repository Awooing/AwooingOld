// ./assets/js/components/Home.js

import React, {Component} from 'react';
import {Route, Switch,Redirect, Link, withRouter} from 'react-router-dom';
import News from './News';

class Home extends Component {

    render() {
        return (
            <div>
                <Switch>
                    <Redirect exact from="/" to="/news" />
                    <Route path="/news" component={News} />
                </Switch>
            </div>
        )
    }
}

export default Home;