import React, {Component} from 'react';
import axios from 'axios';


class News extends Component {
    constructor() {
        super();

        this.state = { posts: [], loading: true}
    }

    componentDidMount() {
        this.getPosts();
    }

    getPosts() {
        axios.get(`http://localhost:3030/awoo/news`).then(res => {
            const posts = res.data.slice(0,15);
            this.setState({ posts, loading: false })
        })
    }

    render() {
        const loading = this.state.loading;
        return (
            <div>
                <section className="row-section">
                    <div className="container">
                        {loading ? (
                            <div className={'row text-center'}>
                                <span className="fa fa-spin fa-spinner fa-4x"></span>
                            </div>

                        ) : (
                            <div className={'row'}>
                                {this.state.posts.map(post =>
                                    <div className="awoo-wrapper" key={post.id}>
                                        <a className="title-link ajax" >{post.title}</a>
                                        <h4 style={{fontSize:"1rem",fontWeight:300,color:"var(--gray)",margin:"0"}} >By {post.user_id} &ndash; {post.created_at}</h4>

                                        {post.content} <p style={{borderRadius: "2rem", marginTop: "4px"}} className="btn btn-primary float-right ajax" >See more</p>

                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </section>
            </div>
        )
    }
}

export default News;